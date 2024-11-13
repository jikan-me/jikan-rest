<?php

namespace App\Features;

use App\Contracts\RequestHandler;
use App\Dto\QueryRandomUserCommand;
use App\Dto\QueryRandomUserListCommand;
use App\Http\Resources\V4\ProfileResource;
use App\Http\Resources\V4\UserCollection;
use App\Profile;
use Spatie\LaravelData\Optional;

/**
 * @extends RequestHandler<QueryRandomUserCommand, UserCollection>
 */
final class QueryRandomUserListHandler implements RequestHandler
{
    /**
     * @inheritDoc
     */
    public function handle($request): UserCollection
    {
        $queryable = Profile::query();
        $limit = $request->limit instanceof Optional ? 1 : $request->limit;
        $results = $queryable->random($limit);

        return new UserCollection($results, false);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomUserListCommand::class;
    }
}
