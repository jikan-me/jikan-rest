<?php

namespace App\Features;

use App\Concerns\ScraperResultCache;
use App\Contracts\RequestHandler;
use App\Dto\QueryTopReviewsCommand;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Http\JsonResponse;
use Jikan\Helper\Constants;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Reviews\RecentReviewsRequest;

/**
 * @implements RequestHandler<QueryTopReviewsCommand, JsonResponse>
 */
class QueryTopReviewsHandler implements RequestHandler
{
    use ScraperResultCache;

    /**
     * @param QueryTopReviewsCommand $request
     * @returns JsonResponse
     */
    public function handle($request): JsonResponse
    {
        $requestParams = collect($request->all());
        $requestFingerPrint = $request->getFingerPrint();
        $results = $this->queryFromScraperCacheByFingerPrint(
            "reviews",
            $requestFingerPrint,
            fn (MalClient $jikan, int $page) => $jikan->getRecentReviews(
                new RecentReviewsRequest(Constants::RECENT_REVIEW_BEST_VOTED, $page)
            ), $requestParams->get("page"));

        return $this->prepareResponse($requestFingerPrint, $results, (new ResultsResource(
            $results->first()
        ))->response());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryTopReviewsCommand::class;
    }
}
