<?php

namespace App\Features;

use App\Dto\MangaStatsLookupCommand;
use App\Http\Resources\V4\MangaStatisticsResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaStatsRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaStatsLookupCommand, JsonResponse>
 */
final class MangaStatsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaStatsLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new MangaStatisticsResource($results);
    }

    /**
     * @param  Collection<array-key, mixed>  $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get('id');

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getMangaStats(new MangaStatsRequest($id))
        );
    }
}
