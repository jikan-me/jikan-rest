<?php

namespace App\Features;

use App\Dto\MangaCharactersLookupCommand;
use App\Http\Resources\V4\MangaCharactersResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaCharactersRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaCharactersLookupCommand, JsonResponse>
 */
final class MangaCharactersLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaCharactersLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new MangaCharactersResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => $jikan->getMangaCharacters(new MangaCharactersRequest($id))
        );
    }
}
