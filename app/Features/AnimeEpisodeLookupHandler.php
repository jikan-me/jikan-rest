<?php

namespace App\Features;

use App\Dto\AnimeEpisodeLookupCommand;
use App\Http\Resources\V4\AnimeEpisodeResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeEpisodeRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeEpisodeLookupCommand, JsonResponse>
 */
final class AnimeEpisodeLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeEpisodeResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeEpisodeLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        $episodeId = $requestParams->get("episodeId");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeEpisode(new AnimeEpisodeRequest($id, $episodeId)),
        );
    }
}
