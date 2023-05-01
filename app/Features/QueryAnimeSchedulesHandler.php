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
        $requestParams = collect($request->all());
        $limit = $requestParams->get("limit");
        $results = $this->repository->getCurrentlyAiring($request->dayFilter);
        // apply sfw, kids and unapproved filters
        /** @noinspection PhpUndefinedMethodInspection */
        $results = $results->filter($requestParams);
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
