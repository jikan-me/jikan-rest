<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomMangaCommand;
use App\Http\Resources\V4\MangaResource;
use App\Manga;

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
        // apply sfw, kids and unapproved filters
        /** @noinspection PhpUndefinedMethodInspection */
        $queryable = $queryable->filter(collect($request->all()));

        return new MangaResource(
            $queryable->random()->first()
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
