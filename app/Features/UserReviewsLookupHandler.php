<?php

namespace App\Features;

use App\Dto\UserReviewsLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserReviewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserReviewsLookupCommand, JsonResponse>
 */
final class UserReviewsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserReviewsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get("username");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getUserReviews(
                new UserReviewsRequest(
                    $username,
                    $page,
                )
            ),
            $requestParams->get("page", 1)
        );
    }
}
