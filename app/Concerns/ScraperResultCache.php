<?php

namespace App\Concerns;

use App\Contracts\Repository;
use App\Http\HttpHelper;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jikan\MyAnimeList\MalClient;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ScraperResultCache
{
    protected function queryFromScraperCacheByFingerPrint(string $cacheTableName, string $requestFingerPrint, \Closure $getMalDataCallback, ?int $page = null): Collection
    {
        $queryable = DB::table($cacheTableName);
        $results = $this->getScraperCacheByFingerPrint($queryable, $requestFingerPrint);

        if (
            $results->isEmpty()
            || $this->isExpired($cacheTableName, $results)
        ) {
            $page = $page ?? 1;
            $data = App::call(function (MalClient $jikan) use ($getMalDataCallback, $page) {
                return $getMalDataCallback($jikan, $page);
            });
            $response = $this->serializeScraperResult($data);
            $results = $this->updateCacheByFingerPrint($queryable, $requestFingerPrint, $results, $response);
        }

        return $results;
    }

    /**
     * @param Repository $repository
     * @param int $id
     * @param string $requestFingerPrint
     * @return Collection
     * @throws NotFoundHttpException
     */
    protected function queryFromScraperCacheById(Repository $repository, int $id, string $requestFingerPrint): Collection
    {
        $results = $repository->getAllByMalId($id);
        $tableName = $repository->tableName();

        if ($results->isEmpty() || $this->isExpired($tableName, $results)) {
            $response = $repository->scrape($id);

            if (HttpHelper::hasError($response)) {
                abort(404, "Resource not found.");
            }

            $results = $this->updateCacheById($repository, $id, $requestFingerPrint, $results, $response);
        }

        if ($results->isEmpty()) {
            abort(404, "Resource not found.");
        }

        return $results;
    }

    private function serializeScraperResult($data): array
    {
        $serializer = app("SerializerV4");
        return $serializer->toArray($data);
    }

    private function prepareScraperResponse(string $requestFingerPrint, Collection $results, array $response): array
    {
        // If resource doesn't exist, prepare meta
        if ($results->isEmpty()) {
            $meta = [
                'createdAt' => new UTCDateTime(),
                'request_hash' => $requestFingerPrint
            ];
        }

        // Update `modifiedAt` meta
        $meta['modifiedAt'] = new UTCDateTime();
        // join meta data with response
        return $meta + $response;
    }

    private function updateCacheById(Repository $repository, int $id, string $requestFingerPrint, Collection $results, array $response): Collection
    {
        $response = $this->prepareScraperResponse($requestFingerPrint, $results, $response);

        if ($results->isEmpty()) {
            $repository->insert($response);
        }

        if ($this->isExpired($repository->tableName(), $results)) {
            $repository->queryByMalId($id)->update($response);
        }

        return $repository->getAllByMalId($id);
    }

    private function updateCacheByFingerPrint(QueryBuilder $queryable, string $requestFingerPrint, Collection $results, array $response): Collection
    {
        $response = $this->prepareScraperResponse($requestFingerPrint, $results, $response);

        // insert cache if resource doesn't exist
        if ($results->isEmpty()) {
            $queryable->insert($response);
        }

        // update cache if resource exists
        if ($this->isExpired($queryable->from, $results)) {
            $this->getQueryableByFingerPrint($queryable, $requestFingerPrint)->update($response);
        }

        return $this->getScraperCacheByFingerPrint($queryable, $requestFingerPrint);
    }

    /**
     * @template T of Response
     * @param string $requestFingerPrint
     * @param Collection $results
     * @param T $response
     * @return T
     */
    protected function prepareResponse(string $requestFingerPrint, Collection $results, $response)
    {
        return $response
            ->header("X-Request-Fingerprint", $requestFingerPrint)
            ->setTtl($this->getTtl())
            ->setExpires(Carbon::createFromTimestamp($this->getExpiry($results)))
            ->setLastModified(Carbon::createFromTimestamp($this->getLastModified($results)));
    }

    protected function getQueryableByFingerPrint(QueryBuilder|EloquentBuilder $queryable, string $requestFingerPrint): EloquentBuilder
    {
        return $queryable->where("request_hash", $requestFingerPrint);
    }

    protected function getScraperCacheByFingerPrint(QueryBuilder|EloquentBuilder $queryable, string $requestFingerPrint): Collection
    {
        return $this->getQueryableByFingerPrint($queryable, $requestFingerPrint)->get();
    }

    private function getLastModified(Collection $results) : ?int
    {
        if (is_array($results->first())) {
            return (int) $results->first()['modifiedAt']->toDateTime()->format('U');
        }

        if (is_object($results->first())) {
            return (int) $results->first()->modifiedAt->toDateTime()->format('U');
        }

        return null;
    }

    protected function getTtl(): int
    {
        return (int) env('CACHE_DEFAULT_EXPIRE');
    }

    private function getExpiry(Collection $results): int
    {
        $modifiedAt = $this->getLastModified($results);
        $ttl = $this->getTtl();
        return $modifiedAt !== null ? $ttl + $modifiedAt : $ttl;
    }

    private function isExpired(string $cacheTableName, Collection $results): bool
    {
        $lastModified = $this->getLastModified($results);

        if ($lastModified === null) {
            return true;
        }

        $expiry = $this->getExpiry($results);

        return time() > $expiry;
    }
}
