<?php

namespace App\Services;

use App\Contracts\CachedScraperService;
use App\Contracts\Repository;
use App\Events\SourceHeartbeatEvent;
use App\Http\HttpHelper;
use App\JikanApiModel;
use App\Providers\SourceHeartbeatProvider;
use App\Support\CachedData;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\SerializerInterface;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A service which scrapes data from MAL if cache is expired or empty.
 *
 * Features:
 * - Retry with exponential backoff (3 attempts: 1s, 2s, 4s delays)
 * - Stale-while-revalidate: serves expired cache when MAL is unreachable
 * - Circuit breaker: skips scraping when MAL health is critically low
 */
final class DefaultCachedScraperService implements CachedScraperService
{
    /**
     * Maximum number of retry attempts for scraping MAL.
     */
    private const MAX_RETRIES = 3;

    /**
     * Base delay in seconds for exponential backoff.
     */
    private const BASE_RETRY_DELAY = 1;

    /**
     * Health score threshold below which scraping is skipped (circuit breaker).
     */
    private const CIRCUIT_BREAKER_THRESHOLD = 0.3;

    public function __construct(
        private readonly Repository $repository,
        private readonly MalClient $jikan,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    /**
     * Finds cached scraper results by cacheKey, if not found scrapes them from MAL via the provided callback.
     * @param string $cacheKey
     * @param \Closure $getMalDataCallback
     * @param int|null $page
     * @return CachedData
     */
    public function findList(string $cacheKey, \Closure $getMalDataCallback, ?int $page = null): CachedData
    {
        $results = $this->get($cacheKey);

        if ($results->isEmpty() || $results->isExpired()) {
            // Circuit breaker: skip scraping if MAL health is critically low
            if ($this->shouldSkipScraping() && !$results->isEmpty()) {
                Log::warning('Circuit breaker active — serving stale cache', ['cacheKey' => $cacheKey]);
                return $results;
            }

            $page = $page ?? 1;

            // Retry with exponential backoff
            $data = $this->retryWithBackoff(function () use ($getMalDataCallback, $page) {
                return $getMalDataCallback($this->jikan, $page);
            }, $cacheKey);

            if ($data !== null) {
                $scraperResponse = $this->serializeScraperResult(Collection::unwrap($data));
                $results = $this->updateCacheByKey($cacheKey, $results, $scraperResponse);
            } elseif (!$results->isEmpty()) {
                // Stale-while-revalidate: serve expired data rather than failing
                Log::info('Serving stale cache after scrape failure', ['cacheKey' => $cacheKey]);
                return $results;
            } else {
                // Upstream outage with no cached fallback available
                abort(503, "Upstream service unavailable.");
            }
        }

        return $results;
    }

    /**
     * Finds cached scraper results by id in the database, if not found scrapes them from MAL.
     * @param int $id
     * @param string $cacheKey
     * @return CachedData
     * @throws NotFoundHttpException
     */
    public function find(int $id, string $cacheKey): CachedData
    {
        $dbResults = $this->repository->getAllByMalId($id);
        $results = $this->dbResultSetToCachedData($dbResults);

        if ($results->isEmpty() || $results->isExpired()) {
            // Circuit breaker: skip scraping if MAL health is critically low
            if ($this->shouldSkipScraping() && !$results->isEmpty()) {
                Log::warning('Circuit breaker active — serving stale cache', ['id' => $id, 'cacheKey' => $cacheKey]);
                return $results;
            }

            // Retry with exponential backoff
            $response = $this->retryWithBackoff(function () use ($id) {
                return $this->repository->scrape($id);
            }, $cacheKey);

            if ($response !== null) {
                // Check if MAL returned an actual error (resource truly doesn't exist)
                if (HttpHelper::hasError($response)) {
                    // Only 404 if we also have no stale data
                    if ($results->isEmpty()) {
                        abort(404, "Resource not found.");
                    }
                    // Otherwise serve stale
                    Log::info('MAL returned error but serving stale cache', ['id' => $id]);
                    return $results;
                }

                $results = $this->updateCacheById($id, $cacheKey, $results, $response);
            } elseif (!$results->isEmpty()) {
                // Stale-while-revalidate: serve expired data rather than failing
                Log::info('Serving stale cache after scrape failure', ['id' => $id, 'cacheKey' => $cacheKey]);
                return $results;
            } else {
                // Upstream outage with no cached fallback available
                abort(503, "Upstream service unavailable.");
            }
        }

        $this->raiseNotFoundIfEmpty($results);

        return $results;
    }

    public function findByKey(string $key, mixed $val, string $cacheKey): CachedData
    {
        $dbResults = $this->repository->where($key, $val)->get();
        $results = $this->dbResultSetToCachedData($dbResults);

        if ($results->isEmpty() || $results->isExpired()) {
            // Circuit breaker
            if ($this->shouldSkipScraping() && !$results->isEmpty()) {
                Log::warning('Circuit breaker active — serving stale cache', ['key' => $key, 'val' => $val]);
                return $results;
            }

            $scraperResponse = $this->retryWithBackoff(function () use ($val) {
                return $this->repository->scrape($val);
            }, $cacheKey);

            if ($scraperResponse !== null) {
                if (HttpHelper::hasError($scraperResponse)) {
                    if ($results->isEmpty()) {
                        abort(404, "Resource not found.");
                    }
                    return $results;
                }

                $response = $this->prepareScraperResponse($cacheKey, $results->isEmpty(), $scraperResponse);
                $response[$key] = $val;

                if ($results->isEmpty()) {
                    $this->repository->insert($response);
                }
                else if ($results->isExpired()) {
                    $this->repository->where($key, $val)->update($response);
                }

                $dbResults = $this->repository->where($key, $val)->get();
                $results = $this->dbResultSetToCachedData($dbResults);
            } elseif (!$results->isEmpty()) {
                Log::info('Serving stale cache after scrape failure', ['key' => $key, 'val' => $val]);
                return $results;
            } else {
                // Upstream outage with no cached fallback available
                abort(503, "Upstream service unavailable.");
            }
        }

        $this->raiseNotFoundIfEmpty($results);

        return $results;
    }

    public function get(string $cacheKey): CachedData
    {
        $dbResults = $this->getByCacheKey($cacheKey);
        return $this->dbResultSetToCachedData($dbResults);
    }

    private function dbResultSetToCachedData(Collection $dbResults): CachedData
    {
        if (!$dbResults->isEmpty()) {
            $item = $dbResults->first();
            return $this->dbRecordToCachedData($item);
        }
        else {
            $item = collect();
        }

        return CachedData::from($item);
    }

    private function dbRecordToCachedData(JikanApiModel|array $item): CachedData
    {
        if ($item instanceof JikanApiModel) {
            return CachedData::fromModel($item);
        }

        return CachedData::fromArray($item);
    }

    private function raiseNotFoundIfEmpty(CachedData $results)
    {
        if ($results->isEmpty()) {
            abort(404, "Resource not found.");
        }
    }

    private function raiseNotFoundIfErrors(mixed $response)
    {
        if (HttpHelper::hasError($response)) {
            abort(404, "Resource not found.");
        }
    }

    private function updateCacheById(int $id, string $cacheKey, CachedData $results, array $scraperResponse): CachedData
    {
        $response = $this->prepareScraperResponse($cacheKey, $results->isEmpty(), $scraperResponse);

        if ($results->isEmpty()) {
            $this->repository->insert($response);
        }
        else if ($results->isExpired()) {
            $this->repository->queryByMalId($id)->update($response);
        }

        $dbResult = $this->repository->getByMalId($id);
        if ($dbResult === null) {
            return CachedData::from(collect());
        }

        return $this->dbRecordToCachedData($dbResult);
    }

    private function updateCacheByKey(string $cacheKey, CachedData $results, array $scraperResponse): CachedData
    {
        $response = $this->prepareScraperResponse($cacheKey, $results->isEmpty(), $scraperResponse);

        // insert cache if resource doesn't exist
        if ($results->isEmpty()) {
            $this->repository->insert($response);
        } else if ($results->isExpired()) {
            $this->getQueryableByCacheKey($cacheKey)->update($response);
        }

        return $this->get($cacheKey);
    }

    private function prepareScraperResponse(string $cacheKey, bool $resultsEmpty, array $scraperResponse): array
    {
        $meta = [];
        if ($resultsEmpty) {
            $meta = [
                // Using Carbon here for testability
                'createdAt' => new UTCDateTime(Carbon::now()->getPreciseTimestamp(3)),
                'request_hash' => $cacheKey
            ];
        }

        // Update `modifiedAt` meta
        // Using Carbon here for testability
        $meta['modifiedAt'] = new UTCDateTime(Carbon::now()->getPreciseTimestamp(3));

        // join meta data with response
        return $meta + $scraperResponse;
    }

    private function getByCacheKey(string $cacheKey): Collection
    {
        return $this->getQueryableByCacheKey($cacheKey)->get();
    }

    private function getQueryableByCacheKey(string $cacheKey): Builder
    {
        return $this->repository->where("request_hash", $cacheKey);
    }

    private function serializeScraperResult(mixed $data): array
    {
        return $this->serializer->toArray($data);
    }

    /**
     * Retry a scraper callback with exponential backoff.
     *
     * @param \Closure $callback The scraper function to call
     * @param string $context Context string for logging
     * @return mixed|null The result, or null if all retries failed
     */
    private function retryWithBackoff(\Closure $callback, string $context): mixed
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $result = $callback();

                // Emit good health on success
                try {
                    event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, 200));
                } catch (\Exception $e) {
                    // Don't break scraping over heartbeat failure
                }

                return $result;

            } catch (\Exception $e) {
                $lastException = $e;
                $statusCode = 0;

                // Extract HTTP status from various exception types
                if (method_exists($e, 'getCode')) {
                    $statusCode = (int) $e->getCode();
                }
                if (method_exists($e, 'getStatusCode')) {
                    $statusCode = (int) $e->getStatusCode();
                }

                // Emit bad health
                try {
                    event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $statusCode));
                } catch (\Exception $he) {
                    // Don't break retrying over heartbeat failure
                }

                // Only retry on server errors and timeouts (not on 404 or client errors)
                $isRetryable = $statusCode === 0 || $statusCode >= 500 || $statusCode === 429;

                if (!$isRetryable) {
                    Log::warning('Non-retryable scraper error', [
                        'context' => $context,
                        'attempt' => $attempt,
                        'status' => $statusCode,
                        'message' => $e->getMessage(),
                    ]);
                    throw $e; // Re-throw non-retryable errors immediately
                }

                if ($attempt < self::MAX_RETRIES) {
                    $delay = self::BASE_RETRY_DELAY * pow(2, $attempt - 1); // 1s, 2s, 4s
                    Log::info('Retrying scraper after failure', [
                        'context' => $context,
                        'attempt' => $attempt,
                        'delay_seconds' => $delay,
                        'status' => $statusCode,
                        'message' => $e->getMessage(),
                    ]);
                    sleep($delay);
                }
            }
        }

        Log::error('All scraper retries exhausted', [
            'context' => $context,
            'max_retries' => self::MAX_RETRIES,
            'last_error' => $lastException ? $lastException->getMessage() : 'unknown',
        ]);

        return null;
    }

    /**
     * Check if scraping should be skipped due to poor MAL health (circuit breaker).
     */
    private function shouldSkipScraping(): bool
    {
        try {
            if (SourceHeartbeatProvider::isFailoverEnabled()) {
                return true;
            }

            $score = SourceHeartbeatProvider::getHeartbeatScore();
            return $score < self::CIRCUIT_BREAKER_THRESHOLD;
        } catch (\Exception $e) {
            // If we can't check health, allow scraping
            return false;
        }
    }
}
