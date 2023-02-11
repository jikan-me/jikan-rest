<?php

namespace App\Features;

use App\Dto\MangaForumLookupCommand;
use App\Http\Resources\V4\ForumResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use App\Support\CachedData;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Manga\MangaForumRequest;

/**
 * @extends RequestHandlerWithScraperCache<MangaForumLookupCommand, JsonResponse>
 */
final class MangaForumLookupHandler extends RequestHandlerWithScraperCache
{
    public function requestClass(): string
    {
        return MangaForumLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ForumResource($results);
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        $filter = $requestParams->get("filter");

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn(MalClient $jikan, ?int $page = null) => collect(
                ["topics" => $jikan->getMangaForum(new MangaForumRequest($id, $filter))]
            )
        );
    }
}
