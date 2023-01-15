<?php

namespace App\Features;

use App\Dto\AnimeStatsLookupCommand;
use App\Http\Resources\V4\AnimeStatisticsResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;

/**
 * @extends RequestHandlerWithScraperCache<AnimeStatsLookupCommand, JsonResponse>
 */
final class AnimeStatsLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeStatisticsResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeStatsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeStats($id)
        );
    }
}
