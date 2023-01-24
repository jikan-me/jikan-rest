<?php

namespace App\Features;

use App\Dto\QueryRecentlyOnlineUsersCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\RecentlyOnlineUsersRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryRecentlyOnlineUsersCommand, JsonResponse>
 */
final class QueryRecentlyOnlineUsersHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryRecentlyOnlineUsersCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => ["results" => $jikan->getRecentOnlineUsers(new RecentlyOnlineUsersRequest())]
        );
    }
}
