<?php

namespace App\Features;

use App\Dto\AnimeForumLookupCommand;
use App\Enums\AnimeForumFilterEnum;
use App\Http\Resources\V4\ForumResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\Anime\AnimeForumRequest;

/**
 * @extends RequestHandlerWithScraperCache<AnimeForumLookupCommand, JsonResponse>
 */
final class AnimeForumLookupHandler extends RequestHandlerWithScraperCache
{
    protected function resource(CachedData $results): JsonResource
    {
        return new ForumResource(
            $results
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeForumLookupCommand::class;
    }

    protected function getScraperData(string $requestFingerPrint, Collection $requestParams): CachedData
    {
        $id = $requestParams->get("id");
        $topic = $requestParams->get("filter", AnimeForumFilterEnum::all()->value);

        return $this->scraperService->findList(
            $requestFingerPrint,
            fn (MalClient $jikan, ?int $page = null) => collect(
                ["topics" => $jikan->getAnimeForum(new AnimeForumRequest($id,  $topic))]
            )
        );
    }
}
