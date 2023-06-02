<?php

namespace App\Features;

use App\Dto\MangaUserUpdatesLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaRecentlyUpdatedByUsersRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaUserUpdatesLookupCommand, JsonResponse>
 */
final class MangaUserUpdatesLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaUserUpdatesLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getMangaRecentlyUpdatedByUsers(new MangaRecentlyUpdatedByUsersRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
