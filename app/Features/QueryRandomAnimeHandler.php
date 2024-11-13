<?php

namespace App\Features;

use App\Anime;
use App\Contracts\RequestHandler;
use App\Dto\QueryRandomAnimeCommand;
use App\Http\Resources\V4\AnimeResource;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomAnimeCommand, AnimeResource>
 */
final class QueryRandomAnimeHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): AnimeResource
    {
        $queryable = Anime::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;

        $results = $queryable->random(1, $sfwParam, $unapprovedParam);

        return new AnimeResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomAnimeCommand::class;
    }
}
