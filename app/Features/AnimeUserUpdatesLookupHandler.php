<?php

namespace App\Features;

use App\Dto\AnimeUserUpdatesLookupCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeRecentlyUpdatedByUsersRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeUserUpdatesLookupCommand, JsonResponse>
 */
final class AnimeUserUpdatesLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeUserUpdatesLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeRecentlyUpdatedByUsers(
                new AnimeRecentlyUpdatedByUsersRequest($id, $page)
            ),
            $requestParams->get("page", 1)
        );
    }
}
