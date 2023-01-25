<?php

namespace App\Features;

use App\Dto\QueryRecentlyAddedPromoVideosCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Watch\RecentPromotionalVideosRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryRecentlyAddedPromoVideosCommand, JsonResponse>
 */
final class QueryRecentlyAddedPromoVideosHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryRecentlyAddedPromoVideosCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getRecentPromotionalVideos(new RecentPromotionalVideosRequest($page)),
            $requestParams->get("page", 1)
        );
    }
}
