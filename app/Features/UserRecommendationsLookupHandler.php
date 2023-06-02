<?php

namespace App\Features;

use App\Dto\UserRecommendationsLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserRecommendationsRequest;

/**
 * @extends RequestHandlerWithScraperCache<UserRecommendationsLookupCommand, JsonResponse>
 */
final class UserRecommendationsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return UserRecommendationsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $username = $requestParams->get("username");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getUserRecommendations(new UserRecommendationsRequest($username, $page)),
            $requestParams->get("page", 1)
        );
    }
}
