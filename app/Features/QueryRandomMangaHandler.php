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
    public function handle($request): MangaResource
    {
        $queryable = Manga::query();

        $sfwParam = $request->sfw instanceof Optional ? false : $request->sfw;
        $unapprovedParam = $request->unapproved instanceof Optional ? false : $request->unapproved;
        $results = $queryable->random(1, $sfwParam, $unapprovedParam);

        return new MangaResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomMangaCommand::class;
    }
}
