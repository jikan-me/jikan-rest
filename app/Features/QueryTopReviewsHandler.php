<?php

namespace App\Features;

use App\Dto\QueryTopReviewsCommand;
use App\Enums\TopReviewsTypeEnum;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\Helper\Constants;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Reviews\ReviewsRequest;

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
        $type = $requestParams->get("type", TopReviewsTypeEnum::anime()->value);
        $spoilers = $requestParams->get("spoilers", true);
        $preliminary = $requestParams->get("preliminary", true);
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getReviews(new ReviewsRequest($type, $page, $spoilers, $preliminary)),
            $requestParams->get("page"));
    }
}
