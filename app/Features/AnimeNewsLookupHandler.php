<?php

namespace App\Features;

use App\Dto\AnimeNewsLookupCommand;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeNewsRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeNewsLookupCommand, JsonResponse>
 */
final class AnimeNewsLookupHandler extends RequestHandlerWithScraperCache
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeNewsLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getNewsList(new AnimeNewsRequest($id, $page)),
            $requestParams->get("page", 1)
        );
    }
}
