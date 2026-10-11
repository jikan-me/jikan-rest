<?php

namespace App\Features;

use App\Dto\QueryTopReviewsCommand;
use App\Enums\TopReviewsTypeEnum;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
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

    /**
     * @param Collection<array-key, mixed> $requestParams
     */
    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        // Note: `?? default` (not get($k, $default)) because a nullable DTO property
        // is present with a null value which would otherwise override the default.
        $type = $requestParams->get("type") ?? TopReviewsTypeEnum::anime()->value;
        $spoilers = (bool) ($requestParams->get("spoilers") ?? true);
        $preliminary = (bool) ($requestParams->get("preliminary") ?? true);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getReviews(new ReviewsRequest(
                ensureEnumPrimitiveValue($type),
                $page,
                \Jikan\Helper\Constants::REVIEWS_SORT_MOST_VOTED,
                $spoilers,
                $preliminary
            )),
            $requestParams->get("page"));
    }
}
