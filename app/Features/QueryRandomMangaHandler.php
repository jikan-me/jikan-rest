<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomMangaCommand;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\MangaResource;
use App\Manga;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomMangaCommand, MangaResource|MangaCollection>
 */
final class QueryRandomMangaHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): MangaResource|MangaCollection
    {
        $queryable = Manga::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit, $sfwParam, $unapprovedParam);

        return $results->count() === 1
            ? new MangaResource($results->first())
            : new MangaCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomMangaCommand::class;
    }
}
