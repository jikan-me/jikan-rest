<?php

namespace App\Features;

use App\Dto\CharacterPicturesLookupCommand;
use App\Http\Resources\V4\PicturesResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Character\CharacterPicturesRequest;

/**
 * @extends RequestHandlerWithScraperCache<CharacterPicturesLookupCommand, JsonResponse>
 */
final class CharacterPicturesLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new PicturesResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterPicturesLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => [
                "pictures" => $jikan->getCharacterPictures(new CharacterPicturesRequest($id))
            ]
        );
    }
}
