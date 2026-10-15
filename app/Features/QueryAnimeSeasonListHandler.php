<?php

namespace App\Features;

use App\Dto\QueryAnimeSeasonListCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\SeasonList\SeasonListRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryAnimeSeasonListCommand, JsonResponse>
 */
final class QueryAnimeSeasonListHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryAnimeSeasonListCommand::class;
    }

    /**
     * @param  Collection<array-key, mixed>  $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getSeasonList(new SeasonListRequest)
        );
    }
}
