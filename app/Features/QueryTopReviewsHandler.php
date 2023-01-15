<?php

namespace App\Features;

use App\Dto\QueryTopReviewsCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\Helper\Constants;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Reviews\RecentReviewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<QueryTopReviewsCommand, JsonResponse>
 */
final class QueryTopReviewsHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryTopReviewsCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getRecentReviews(new RecentReviewsRequest(Constants::RECENT_REVIEW_BEST_VOTED, $page)),
            $requestParams->get("page"));
    }
}
