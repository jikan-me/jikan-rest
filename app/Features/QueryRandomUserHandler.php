<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomUserCommand;
use App\Http\Resources\V4\ProfileResource;
use App\Http\Resources\V4\UserCollection;
use App\Profile;
use Spatie\LaravelData\Optional;

/**
 * @extends RequestHandler<QueryRandomUserCommand, ProfileResource|UserCollection>
 */
final class QueryRandomUserHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): ProfileResource|UserCollection
    {
        $queryable = Profile::query();

        $limit = $request->limit instanceof Optional ? 1 : $request->limit;

        $results = $queryable->random($limit);

        return $results->count() === 1
            ? new ProfileResource($results->first())
            : new UserCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomUserCommand::class;
    }
}
