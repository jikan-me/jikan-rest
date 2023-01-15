<?php

namespace App\Features;

use App\Dto\UserByIdLookupCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UsernameByIdRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserByIdLookupCommand, JsonResponse>
 */
final class UserByIdLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserByIdLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => collect(['results' => $jikan->getUsernameById(new UsernameByIdRequest($requestParams->get("id")))])
        );
    }
}
