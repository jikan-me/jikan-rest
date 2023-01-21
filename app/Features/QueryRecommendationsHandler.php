<?php

namespace App\Features;

use App\Contracts\DataRequest;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Recommendations\RecentRecommendationsRequest;

/**
 * @template TRequest of DataRequest<ResultsResource>
 * @extends RequestHandlerWithScraperCache<TRequest, ResultsResource>
 */
abstract class QueryRecommendationsHandler extends RequestHandlerWithScraperCache
{
    protected abstract function recommendationType(): string;

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getRecentRecommendations(new RecentRecommendationsRequest(
                $this->recommendationType(), $page
            )),
            $requestParams->get("page", 1)
        );
    }
}
