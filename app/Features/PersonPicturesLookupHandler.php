<?php

namespace App\Features;

use App\Dto\PersonPicturesLookupCommand;
use App\Http\Resources\V4\PicturesResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Person\PersonPicturesRequest;

/**
 * @extends RequestHandlerWithScraperCache<PersonPicturesLookupCommand, JsonResponse>
 */
final class PersonPicturesLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return PersonPicturesLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new PicturesResource($results->first());
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => collect(
                ["pictures" => $jikan->getPersonPictures(new PersonPicturesRequest($id))]
            )
        );
    }
}
