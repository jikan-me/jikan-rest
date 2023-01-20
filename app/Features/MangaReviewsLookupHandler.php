<?php

namespace App\Features;

use App\Dto\MangaReviewsLookupCommand;
use App\Features\Concerns\ResolvesMediaReviewParams;
use App\Http\Resources\V4\ReviewsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaReviewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaReviewsLookupCommand, JsonResponse>
 */
final class MangaReviewsLookupHandler extends RequestHandlerWithScraperCache
{
    use ResolvesMediaReviewParams;

    public function requestClass(): string
    {
        return MangaReviewsLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ReviewsResource($results->first());
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $reviewRequestParams = $this->getReviewRequestParams($requestParams);
        // import array members as variables into the current scope's symbol table
        extract($reviewRequestParams);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getMangaReviews(new MangaReviewsRequest(
                $id, $page, $sort, $spoilers, $preliminary
            )),
            $requestParams->get("page", 1)
        );
    }
}
