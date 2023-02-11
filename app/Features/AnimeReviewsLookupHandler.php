<?php

namespace App\Features;

use App\Dto\AnimeReviewsLookupCommand;
use App\Enums\MediaReviewsSortEnum;
use App\Features\Concerns\ResolvesMediaReviewParams;
use App\Http\Resources\V4\ReviewsResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeReviewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeReviewsLookupCommand, JsonResponse>
 */
final class AnimeReviewsLookupHandler extends RequestHandlerWithScraperCache
{
    use ResolvesMediaReviewParams;

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeReviewsLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ReviewsResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $reviewRequestParams = $this->getReviewRequestParams($requestParams);
        // import array members as variables into the current scope's symbol table
        extract($reviewRequestParams);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeReviews(new AnimeReviewsRequest(
                $id, $page, $sort, $spoilers, $preliminary
            )),
            $requestParams->get("page", 1)
        );
    }
}
