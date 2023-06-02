<?php

namespace App\Features;

use App\Dto\MangaPicturesLookupCommand;
use App\Http\Resources\V4\PicturesResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaPicturesRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaPicturesLookupCommand, JsonResponse>
 */
final class MangaPicturesLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaPicturesLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new PicturesResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => collect(
                ["pictures" => $jikan->getMangaPictures(new MangaPicturesRequest($id))]
            )
        );
    }
}
