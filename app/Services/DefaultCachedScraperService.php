<?php

namespace App\Services;

use App\Contracts\CachedScraperService;
use App\Contracts\Repository;
use App\Http\HttpHelper;
use App\JikanApiModel;
use App\Support\CachedData;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use JMS\Serializer\SerializerInterface;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A service which scrapes data from MAL if cache is expired or empty
 */
final class DefaultCachedScraperService implements CachedScraperService
{
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
            $page = $page ?? 1;

            // most of the time callback uses a call to the jikan lib, which scrapes some info from MAL
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
        $dbResults = $this->repository->getAllByMalId($id);
        $results = $this->dbResultSetToCachedData($dbResults);

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
        $dbResults = $this->repository->where($key, $val)->get();
        $results = $this->dbResultSetToCachedData($dbResults);

        if ($results->isEmpty() || $results->isExpired()) {
            $scraperResponse = $this->repository->scrape($val);

            $this->raiseNotFoundIfErrors($scraperResponse);

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
}
