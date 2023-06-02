<?php

namespace App\Features;

use App\Contracts\PeopleRepository;
use App\Dto\QueryRandomPersonCommand;
use App\Http\Resources\V4\PersonResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends QueryRandomItemHandler<QueryRandomPersonCommand, PersonResource>
 */
final class QueryRandomPersonHandler extends QueryRandomItemHandler
{
    public function __construct(PeopleRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomPersonCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new PersonResource($results->first());
    }
}
