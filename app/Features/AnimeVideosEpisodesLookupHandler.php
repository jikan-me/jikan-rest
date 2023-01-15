<?php

namespace App\Features;

use App\Dto\AnimeVideosEpisodesLookupCommand;
use App\Http\Resources\V4\AnimeEpisodesResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeVideosEpisodesRequest;


/**
 * @extends RequestHandlerWithScraperCache<AnimeVideosEpisodesLookupCommand, JsonResponse>
 */
final class AnimeVideosEpisodesLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeEpisodesResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeVideosEpisodesLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeVideosEpisodes(new AnimeVideosEpisodesRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
