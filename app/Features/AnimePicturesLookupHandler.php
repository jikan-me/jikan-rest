<?php

namespace App\Features;

use App\Dto\AnimePicturesLookupCommand;
use App\Http\Resources\V4\PicturesResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimePicturesRequest;


/**
 * @extends RequestHandlerWithScraperCache<AnimePicturesLookupCommand, JsonResponse>
 */
final class AnimePicturesLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new PicturesResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimePicturesLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => collect(
                ["pictures" => $jikan->getAnimePictures(new AnimePicturesRequest($id))]
            )
        );
    }
}
