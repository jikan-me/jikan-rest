<?php

namespace App\Features;

use App\Contracts\UserRepository;
use App\Dto\QueryRandomUserCommand;
use App\Http\Resources\V4\ProfileResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends QueryRandomItemHandler<QueryRandomUserCommand, ProfileResource>
 */
final class QueryRandomUserHandler extends QueryRandomItemHandler
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ProfileResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return QueryRandomUserCommand::class;
    }
}
