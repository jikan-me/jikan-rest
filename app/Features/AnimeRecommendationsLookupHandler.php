<?php

namespace App\Features;

use App\Dto\AnimeRecommendationsLookupCommand;
use App\Http\Resources\V4\RecommendationsResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeRecommendationsRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeRecommendationsLookupCommand, JsonResponse>
 */
final class AnimeRecommendationsLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new RecommendationsResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeRecommendationsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => collect(
                ["recommendations" => $jikan->getAnimeRecommendations(new AnimeRecommendationsRequest($id))]
            )
        );
    }
}
