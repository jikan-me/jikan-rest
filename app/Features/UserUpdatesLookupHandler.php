<?php

namespace App\Features;

use App\Dto\UserUpdatesLookupCommand;
use App\Http\Resources\V4\ProfileLastUpdatesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserUpdatesLookupCommand>
 */
final class UserUpdatesLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserUpdatesLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ProfileLastUpdatesResource($results->first());
    }
}
