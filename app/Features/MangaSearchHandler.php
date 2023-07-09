<?php

namespace App\Features;

use App\Dto\MangaSearchCommand;
use App\Enums\MangaOrderByEnum;
use App\Http\Resources\V4\MangaCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends SearchRequestHandler<MangaSearchCommand, MangaCollection>
 */
class MangaSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return MangaSearchCommand::class;
    }

    /**
     * @inheritDoc
     */
    protected function renderResponse(LengthAwarePaginator $paginator)
    {
        return new MangaCollection($paginator);
    }
}
