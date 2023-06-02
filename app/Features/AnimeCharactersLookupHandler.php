<?php

namespace App\Features;

use App\Dto\AnimeCharactersLookupCommand;
use App\Http\Resources\V4\AnimeCharactersResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeCharactersAndStaffRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeCharactersLookupCommand, JsonResponse>
 */
final class AnimeCharactersLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(CachedData $results): JsonResource
    {
        return new AnimeCharactersResource($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeCharactersLookupCommand::class;
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
