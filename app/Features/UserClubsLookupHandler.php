<?php

namespace App\Features;

use App\Dto\UserClubsLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserClubsRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserClubsLookupCommand, JsonResponse>
 */
final class UserClubsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserClubsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get("username");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => ["results" => $jikan->getUserClubs(new UserClubsRequest($username))]
        );
    }
}
