<?php

namespace App\Features;

use App\Dto\AnimeEpisodesLookupCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeEpisodesRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeEpisodesLookupCommand, JsonResponse>
 */
final class AnimeEpisodesLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeEpisodesLookupCommand::class;
    }

    /**
     * @param Collection<array-key, mixed> $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeEpisodes(new AnimeEpisodesRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
