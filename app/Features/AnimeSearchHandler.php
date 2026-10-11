<?php

namespace App\Features;

use App\Dto\AnimeSearchCommand;
use App\Http\Resources\V4\AnimeCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<AnimeSearchCommand, AnimeCollection>
 */
class AnimeSearchHandler extends SearchRequestHandler
{
    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeSearchCommand::class;
    }

    protected function renderResponse(LengthAwarePaginator $paginator): AnimeCollection
    {
        return new AnimeCollection($paginator);
    }
}
