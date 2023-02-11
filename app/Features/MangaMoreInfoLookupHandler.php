<?php

namespace App\Features;

use App\Dto\MangaMoreInfoLookupCommand;
use App\Http\Resources\V4\MoreInfoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaMoreInfoRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaMoreInfoLookupCommand, JsonResponse>
 */
final class MangaMoreInfoLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaMoreInfoLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new MoreInfoResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => collect(
                ["moreinfo" => $jikan->getMangaMoreInfo(new MangaMoreInfoRequest($id))]
            )
        );
    }
}
