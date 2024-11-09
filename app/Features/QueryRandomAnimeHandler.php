<?php

namespace App\Features;

use App\Anime;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomAnimeCommand;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\AnimeResource;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomAnimeCommand, AnimeResource|AnimeCollection>
 */
final class QueryRandomAnimeHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): AnimeResource|AnimeCollection
    {
        $queryable = Anime::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit, $sfwParam, $unapprovedParam);

        return $results->count() === 1
            ? new AnimeResource($results->first())
            : new AnimeCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomAnimeCommand::class;
    }
}
