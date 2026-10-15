<?php

namespace App\Features;

use App\Dto\QueryPopularPromoVideosCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Watch\PopularPromotionalVideosRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryPopularPromoVideosCommand, JsonResponse>
 */
final class QueryPopularPromoVideosHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return QueryPopularPromoVideosCommand::class;
    }

    /**
     * @param  Collection<array-key, mixed>  $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getPopularPromotionalVideos(new PopularPromotionalVideosRequest)
        );
    }
}
