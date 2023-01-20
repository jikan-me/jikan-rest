<?php

namespace App\Features;

use App\Dto\MangaNewsLookupCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaNewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaNewsLookupCommand, JsonResponse>
 */
final class MangaNewsLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaNewsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getNewsList(new MangaNewsRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
