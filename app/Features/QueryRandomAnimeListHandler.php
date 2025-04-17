<?php

namespace App\Features;

use App\Anime;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomAnimeCommand;
use App\Dto\QueryRandomAnimeListCommand;
use App\Http\Resources\V4\AnimeCollection;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomAnimeCommand, AnimeCollection>
 */
final class QueryRandomAnimeListHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): AnimeCollection
    {
        $queryable = Anime::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit, $sfwParam, $unapprovedParam);

        return new AnimeCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomAnimeListCommand::class;
    }
}
