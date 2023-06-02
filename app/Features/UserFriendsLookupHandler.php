<?php

namespace App\Features;

use App\Dto\UserFriendsLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserFriendsRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserFriendsLookupCommand, JsonResponse>
 */
final class UserFriendsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserFriendsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get("username");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getUserFriends(new UserFriendsRequest($username, $page)),
            $requestParams->get("page", 1)
        );
    }
}
