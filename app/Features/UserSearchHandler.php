<?php

namespace App\Features;

use App\Dto\UsersSearchCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Search\UserSearchRequest;

/**
 * @implements RequestHandlerWithScraperCache<UsersSearchCommand, JsonResponse>
 */
final class UserSearchHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return UsersSearchCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, int $page) => $jikan->getUserSearch((new UserSearchRequest())
                ->setQuery($requestParams->get("q"))
                ->setGender($requestParams->get("gender"))
                ->setLocation($requestParams->get("location"))
                ->setMaxAge($requestParams->get("maxAge"))
                ->setMinAge($requestParams->get("minAge"))
                ->setPage($page)),
            $requestParams->get("page")
        );
    }
}
