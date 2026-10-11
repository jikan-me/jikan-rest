<?php

namespace App\Features;

use App\Dto\ClubSearchCommand;
use App\Http\Resources\V4\ClubCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<ClubSearchCommand, ClubCollection>
 */
final class ClubSearchHandler extends SearchRequestHandler
{

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return ClubSearchCommand::class;
    }

    /**
     * @param LengthAwarePaginator<int, mixed> $paginator
     * @return ClubCollection
     */
    protected function renderResponse(LengthAwarePaginator $paginator): ClubCollection
    {
        return new ClubCollection($paginator);
    }
}
