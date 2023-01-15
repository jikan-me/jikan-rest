<?php

namespace App\Features;

use App\Dto\AnimeStaffLookupCommand;
use App\Http\Resources\V4\AnimeStaffResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeStaffLookupCommand, JsonResponse>
 */
final class AnimeStaffLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeStaffResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeStaffLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => $jikan->getAnimeCharactersAndStaff(new AnimeCharactersAndStaffRequest($id))
        );
    }
}
