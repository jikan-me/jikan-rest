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
     * @inheritDoc
     */
    public function handle($request)
    {
        $queryable = Manga::query();

        $o = Optional::create();
        $sfwParam = $request->sfw === $o ? false : $request->sfw;
        $unapprovedParam = $request->unapproved === $o ? false : $request->unapproved;

        return new MangaResource(
            $queryable->random(1, $sfwParam, $unapprovedParam)->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomMangaCommand::class;
    }
}
