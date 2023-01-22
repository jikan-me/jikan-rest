<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryUpcomingAnimeSeasonCommand;
use App\Http\Resources\V4\AnimeCollection;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;

/**
 * @implements RequestHandler<QueryUpcomingAnimeSeasonCommand, JsonResponse>
 */
final class QueryUpcomingAnimeSeasonHandler implements RequestHandler
{
    public function __construct(protected readonly AnimeRepository $repository)
    {
    }

    public function handle($request): JsonResponse
    {
        $type = collect($request->all())->has("filter") ? $request->filter : null;
        $results = $this->repository->getUpcomingSeasonItems($type);
        $results = $results->paginate($request->limit, ["*"], null, $request->page);

        $animeCollection = new AnimeCollection($results);
        $response = $animeCollection->response();

        return $response->addJikanCacheFlags($request->getFingerPrint(), CachedData::from($animeCollection->collection));
    }

    public function requestClass(): string
    {
        return QueryUpcomingAnimeSeasonCommand::class;
    }
}
