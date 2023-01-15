<?php

namespace App\Contracts;

use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Representation of a service which knows about cached MAL scraper results.
 */
interface CachedScraperService
{
    /**
     * Finds cached scraper results by cacheKey, if not found scrapes them from MAL via the provided callback.
     * @param string $cacheKey
     * @param \Closure $getMalDataCallback
     * @param int|null $page
     * @return CachedData
     */
    public function findList(string $cacheKey, \Closure $getMalDataCallback, ?int $page = null): CachedData;

    /**
     * Finds cached scraper results by id in the database, if not found scrapes them from MAL.
     * @param int $id
     * @param string $cacheKey
     * @return CachedData
     * @throws NotFoundHttpException
     */
    public function find(int $id, string $cacheKey): CachedData;

    public function findByKey(string $key, mixed $val, string $cacheKey): CachedData;

    public function get(string $cacheKey): CachedData;

    public function augmentResponse(JsonResponse|Response $response, string $cacheKey, CachedData $scraperResults): JsonResponse|Response;
}
