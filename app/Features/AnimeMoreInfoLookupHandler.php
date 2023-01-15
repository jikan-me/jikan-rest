<?php

namespace App\Features;

use App\Dto\AnimeStatsLookupCommand;
use App\Http\Resources\V4\MoreInfoResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeMoreInfoRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeStatsLookupCommand, JsonResponse>
 */
final class AnimeMoreInfoLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new MoreInfoResource(
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
            fn (MalClient $jikan, ?int $page = null) => collect(
                ["moreinfo" => $jikan->getAnimeMoreInfo(new AnimeMoreInfoRequest($id))]
            )
        );
    }
}
