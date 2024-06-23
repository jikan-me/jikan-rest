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

        $o = Optional::create();
        $sfwParam = $request->sfw === $o ? false : $request->sfw;
        $unapprovedParam = $request->unapproved === $o ? false : $request->unapproved;

        return new AnimeResource(
            $queryable->random(1, $sfwParam, $unapprovedParam)->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomAnimeCommand::class;
    }
}
