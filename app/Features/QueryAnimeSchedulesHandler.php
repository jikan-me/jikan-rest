<?php

namespace App\Features;

use App\Contracts\AnimeRepository;
use App\Contracts\RequestHandler;
use App\Dto\QueryAnimeSchedulesCommand;
use App\Http\Resources\V4\AnimeCollection;
use App\Support\CachedData;

/**
 * @implements RequestHandler<QueryAnimeSchedulesCommand, AnimeCollection>
 */
final class QueryAnimeSchedulesHandler implements RequestHandler
{
    public function __construct(private readonly AnimeRepository $repository) {}

    /**
     * {@inheritDoc}
     */
    public function handle($request)
    {
        \Log::info('QueryAnimeSchedulesHandler::handle called', ['filter' => $request->filter]);
        $requestParams = collect($request->all());
        $limit = $requestParams->get('limit');
        $results = $this->repository->getCurrentlyAiring($request->filter);
        \Log::info('QueryAnimeSchedulesHandler: results count', ['count' => $results->count()]);
        // apply sfw, kids and unapproved filters
        /** @noinspection PhpUndefinedMethodInspection */
        $results = $results->filter($requestParams);
        \Log::info('QueryAnimeSchedulesHandler: after filter count', ['count' => $results->count()]);
        $results = $results->paginate(
            $limit,
            ['*'],
            null,
            $request->page
        );
        \Log::info('QueryAnimeSchedulesHandler: paginated', ['count' => $results->count(), 'total' => $results->total()]);

        $animeCollection = new AnimeCollection(
            $results
        );
        $response = $animeCollection->response();

        return $response->addJikanCacheFlags($request->getFingerPrint(), CachedData::from($animeCollection->collection));
    }

    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return QueryAnimeSchedulesCommand::class;
    }
}
