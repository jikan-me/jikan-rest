<?php

namespace App\Features;

use App\Dto\AnimeVideosLookupCommand;
use App\Http\Resources\V4\AnimeVideosResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeVideosRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeVideosLookupCommand, JsonResponse>
 */
final class AnimeVideosLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeVideosResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeVideosLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeVideos(new AnimeVideosRequest($id))
        );
    }
}
