<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomUserCommand;
use App\Http\Resources\V4\ProfileResource;
use App\Profile;

/**
 * @extends RequestHandler<QueryRandomUserCommand, ProfileResource>
 */
final class QueryRandomUserHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): ProfileResource
    {
        $queryable = Profile::query();
        $results = $queryable->random(1);

        return new ProfileResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomUserCommand::class;
    }
}
