<?php

namespace App\Features;

use App\Dto\QueryPopularEpisodesCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Watch\PopularEpisodesRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryPopularEpisodesCommand, JsonResponse>
 */
final class QueryPopularEpisodesHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryPopularEpisodesCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getPopularEpisodes(new PopularEpisodesRequest())
        );
    }
}
