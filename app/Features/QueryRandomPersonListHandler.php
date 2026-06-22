<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomPersonCommand;
use App\Dto\QueryRandomPersonListCommand;
use App\Http\Resources\V4\PersonCollection;
use App\Person;
use Spatie\LaravelData\Optional;

/**
 * @extends RequestHandler<QueryRandomPersonCommand, PersonCollection>
 */
final class QueryRandomPersonListHandler implements RequestHandler
{

    /**
     * @inheritDoc
     */
    public function handle($request): PersonCollection
    {
        $queryable = Person::query();
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;
        $results = $queryable->random($limit);

        return new PersonCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomPersonListCommand::class;
    }
}
