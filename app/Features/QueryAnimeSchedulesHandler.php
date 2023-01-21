<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryAnimeSchedulesCommand;
use App\Http\Resources\V4\AnimeCollection;
use App\Support\CachedData;
use Illuminate\Support\Env;

/**
 * @implements RequestHandler<QueryAnimeSchedulesCommand, AnimeCollection>
 */
final class QueryAnimeSchedulesHandler implements RequestHandler
{
    public function __construct(private readonly AnimeRepository $repository)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($request)
    {
        $limit = intval($request->limit ?? Env::get("MAX_RESULTS_PER_PAGE", 25));
        $results = $this->repository->getCurrentlyAiring($request->filter, $request->kids, $request->sfw);
        $results = $results->paginate(
            $limit,
            ["*"],
            null,
            $request->page
        );

        $animeCollection = new AnimeCollection(
            $results
        );
        $response = $animeCollection->response();

        return $response->addJikanCacheFlags($request->getFingerPrint(), CachedData::from($animeCollection->collection));
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryAnimeSchedulesCommand::class;
    }
}
