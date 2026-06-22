<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomPersonCommand;
use App\Http\Resources\V4\PersonResource;
use App\Person;

/**
 * @extends RequestHandler<QueryRandomPersonCommand, PersonResource>
 */
final class QueryRandomPersonHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function handle($request): PersonResource
    {
        return new PersonResource(
            Person::query()
                ->random()
                ->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomPersonCommand::class;
    }
}
