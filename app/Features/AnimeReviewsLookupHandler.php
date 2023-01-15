<?php

namespace App\Features;

use App\Dto\AnimeReviewsLookupCommand;
use App\Enums\AnimeReviewsSortEnum;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeReviewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeReviewsLookupCommand, JsonResponse>
 */
final class AnimeReviewsLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeReviewsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        $sort = $requestParams->get("sort", AnimeReviewsSortEnum::mostVoted()->value);
        $spoilers = $requestParams->get("spoilers", false);
        $preliminary = $requestParams->get("preliminary", false);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeReviews(new AnimeReviewsRequest(
                $id, $page, $sort, $spoilers, $preliminary
            )),
            $requestParams->get("page", 1)
        );
    }
}
