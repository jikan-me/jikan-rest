<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomPersonCommand;
use App\Http\Resources\V4\PersonCollection;
use App\Http\Resources\V4\PersonResource;
use App\Person;
use Spatie\LaravelData\Optional;

/**
 * @extends RequestHandler<QueryRandomPersonCommand, PersonResource|PersonCollection>
 */
final class QueryRandomPersonHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function handle($request): PersonResource|PersonCollection
    {
        $queryable = Person::query();

        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit);

        return $results->count() === 1
            ? new PersonResource($results->first())
            : new PersonCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomPersonCommand::class;
    }
}
