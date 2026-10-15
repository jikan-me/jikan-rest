<?php

namespace App\Contracts;

use App\Support\CachedData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Representation of a service which knows about cached MAL scraper results.
 */
interface CachedScraperService
{
    /**
     * Finds cached scraper results by cacheKey, if not found scrapes them from MAL via the provided callback.
     */
    public function findList(string $cacheKey, \Closure $getMalDataCallback, ?int $page = null): CachedData;

    /**
     * Finds cached scraper results by id in the database, if not found scrapes them from MAL.
     *
     * @throws NotFoundHttpException
     */
    public function find(int $id, string $cacheKey): CachedData;

    public function findByKey(string $key, mixed $val, string $cacheKey): CachedData;

    public function get(string $cacheKey): CachedData;
}
