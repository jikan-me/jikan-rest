<?php

namespace App\Features;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;

/**
 * @template TRequest
 * @extends RequestHandlerWithScraperCache<TRequest, JsonResponse>
 */
abstract class UserLookupHandler extends RequestHandlerWithScraperCache
{
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get("username");

        return $this->scraperService->findByKey(
            "internal_username",
            $username,
            $requestFingerPrint,
        );
    }
}
