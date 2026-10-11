<?php

namespace App\Features;

use App\Dto\PeopleSearchCommand;
use App\Http\Resources\V4\PersonCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends SearchRequestHandler<PeopleSearchCommand, PersonCollection>
 */
class PeopleSearchHandler extends SearchRequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return PeopleSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): PersonCollection
    {
        return new PersonCollection($paginator);
    }
}
