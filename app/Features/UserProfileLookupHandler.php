<?php

namespace App\Features;

use App\Dto\UserProfileLookupCommand;
use App\Http\Resources\V4\ProfileResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserProfileLookupCommand>
 */
final class UserProfileLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserProfileLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ProfileResource($results->first());
    }
}
