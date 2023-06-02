<?php

namespace App\Features;

use App\Dto\QueryRecentlyAddedEpisodesCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Watch\RecentEpisodesRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryRecentlyAddedEpisodesCommand, JsonResponse>
 */
final class QueryRecentlyAddedEpisodesHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryRecentlyAddedEpisodesCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getRecentEpisodes(new RecentEpisodesRequest())
        );
    }
}
