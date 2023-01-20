<?php

namespace App\Features;

use App\Dto\MangaRecommendationsLookupCommand;
use App\Http\Resources\V4\RecommendationsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaRecommendationsRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaRecommendationsLookupCommand, JsonResponse>
 */
final class MangaRecommendationsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaRecommendationsLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new RecommendationsResource($results->first());
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => collect(
                ["recommendations" => $jikan->getMangaRecommendations(new MangaRecommendationsRequest($id))]
            )
        );
    }
}
