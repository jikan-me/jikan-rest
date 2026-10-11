<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomMangaCommand;
use App\Http\Resources\V4\MangaResource;
use App\Manga;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomMangaCommand, MangaResource>
 */
final class QueryRandomMangaHandler implements RequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function handle($request)
    {
        $queryable = Manga::query();

        $o = Optional::create();
        $sfwParam = $request->sfw === $o ? false : $request->sfw;
        $unapprovedParam = $request->unapproved === $o ? false : $request->unapproved;

        $manga = $queryable->random(1, $sfwParam, $unapprovedParam)->first();

        if ($manga === null) {
            // no manga indexed -> 404 instead of a null-crash in the resource
            abort(404);
        }

        return new MangaResource($manga);
    }

    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return QueryRandomMangaCommand::class;
    }
}
