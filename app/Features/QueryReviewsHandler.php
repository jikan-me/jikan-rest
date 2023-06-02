<?php

namespace App\Features;

use App\Contracts\DataRequest;
use App\Enums\ReviewTypeEnum;
use App\Features\Concerns\ResolvesMediaReviewParams;
use App\Http\Resources\V4\ResultsResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Reviews\ReviewsRequest;

/**
 * @template TRequest of DataRequest<ResultsResource>
 * @extends RequestHandlerWithScraperCache<TRequest, ResultsResource>
 */
abstract class QueryReviewsHandler extends RequestHandlerWithScraperCache
{
    use ResolvesMediaReviewParams;

    protected abstract function reviewType(): ReviewTypeEnum;

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $reviewRequestParams = $this->getReviewRequestParams($requestParams);
        extract($reviewRequestParams);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getReviews(
                new ReviewsRequest(
                    $this->reviewType()->value,
                    $page,
                    $sort,
                    $spoilers,
                    $preliminary
                )
            )
        );
    }
}
