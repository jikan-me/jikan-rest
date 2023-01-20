<?php

namespace App\Services;

use App\Concerns\ScraperCacheTtl;
use App\Contracts\CachedScraperService;
use App\Contracts\Repository;
use App\Http\HttpHelper;
use App\Support\CachedData;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use MongoDB\BSON\UTCDateTime;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A service which scrapes data from MAL if cache is expired or empty
 */
final class DefaultCachedScraperService implements CachedScraperService
{
    use ScraperCacheTtl;

    public function __construct(
        private readonly Repository $repository,
        private readonly MalClient $jikan,
        private readonly Serializer $serializer,
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
            $page = $page ?? 1;
            $data = $getMalDataCallback($this->jikan, $page);
            $scraperResponse = $this->serializeScraperResult(Collection::unwrap($data));
            $results = $this->updateCacheByKey($cacheKey, $results, $scraperResponse);
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
        $results = CachedData::from($this->repository->getAllByMalId($id));

        if ($results->isEmpty() || $results->isExpired()) {
            $response = $this->repository->scrape($id);

            $this->raiseNotFoundIfErrors($response);

            $results = $this->updateCacheById($id, $cacheKey, $results, $response);
        }

        $this->raiseNotFoundIfEmpty($results);

        return $results;
    }

    public function findByKey(string $key, mixed $val, string $cacheKey): CachedData
    {
        $results = CachedData::from($this->repository->where($key, $val)->get());

        if ($results->isEmpty() || $results->isExpired()) {
            $scraperResponse = $this->repository->scrape($key);

            $this->raiseNotFoundIfErrors($scraperResponse);

            $response = $this->prepareScraperResponse($cacheKey, $results->isEmpty(), $scraperResponse);
            $response->offsetSet($key, $val);

            if ($results->isEmpty()) {
                $this->repository->insert($response->toArray());
            }

            if ($results->isExpired()) {
                $this->repository->where($key, $val)->update($response->toArray());
            }

            $results = CachedData::from($this->repository->where($key, $val)->get());
        }

        $this->raiseNotFoundIfEmpty($results);

        return $results;
    }

    public function get(string $cacheKey): CachedData
    {
        return CachedData::from($this->getByCacheKey($cacheKey));
    }

    public function augmentResponse(JsonResponse|Response $response, string $cacheKey, CachedData $scraperResults): JsonResponse|Response
    {
        return $response
            ->header("X-Request-Fingerprint", $cacheKey)
            ->setTtl($this->cacheTtl())
            ->setExpires(Carbon::createFromTimestamp($scraperResults->expiry()))
            ->setLastModified(Carbon::createFromTimestamp($scraperResults->lastModified()));
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
            $this->repository->insert($response->toArray());
        }

        if ($results->isExpired()) {
            $this->repository->queryByMalId($id)->update($response->toArray());
        }

        return new CachedData(collect($this->repository->getAllByMalId($id)));
    }

    private function updateCacheByKey(string $cacheKey, CachedData $results, array $scraperResponse): CachedData
    {
        $response = $this->prepareScraperResponse($cacheKey, $results->isEmpty(), $scraperResponse);

        // insert cache if resource doesn't exist
        if ($results->isEmpty()) {
            $this->repository->insert($response->toArray());
        }

        if ($results->isExpired()) {
            $this->getQueryableByCacheKey($cacheKey)->update($response->toArray());
        }

        return new CachedData($this->getByCacheKey($cacheKey));
    }

    private function prepareScraperResponse(string $cacheKey, bool $resultsEmpty, array $scraperResponse): CachedData
    {
        $meta = [];
        if ($resultsEmpty) {
            $meta = [
                'createdAt' => new UTCDateTime(),
                'request_hash' => $cacheKey
            ];
        }

        // Update `modifiedAt` meta
        $meta['modifiedAt'] = new UTCDateTime();

        // join meta data with response
        return new CachedData(collect($meta + $scraperResponse));
    }

    private function getByCacheKey(string $cacheKey): Collection
    {
        return $this->getQueryableByCacheKey($cacheKey)->get();
    }

    private function getQueryableByCacheKey(string $cacheKey): Builder
    {
        return $this->repository->where("request_hash", $cacheKey);
    }

    private function serializeScraperResult(array $data): array
    {
        return $this->serializer->toArray($data);
    }
}
