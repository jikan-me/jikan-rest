<?php

namespace App\Features;

use App\Contracts\DataRequest;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * @template TRequest of DataRequest
 *
 * @extends RequestHandlerWithScraperCache<TRequest, JsonResponse>
 */
abstract class UserLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @param  Collection<array-key, mixed>  $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get('username');

        return $this->scraperService->findByKey(
            'internal_username',
            $username,
            $requestFingerPrint,
        );
    }
}
