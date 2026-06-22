<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomMangaCommand;
use App\Dto\QueryRandomMangaListCommand;
use App\Http\Resources\V4\MangaCollection;
use App\Manga;
use Spatie\LaravelData\Optional;

/**
 * @implements RequestHandler<QueryRandomMangaCommand, MangaCollection>
 */
final class QueryRandomMangaListHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): MangaCollection
    {
        $queryable = Manga::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit, $sfwParam, $unapprovedParam);

        return new MangaCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomMangaListCommand::class;
    }
}
