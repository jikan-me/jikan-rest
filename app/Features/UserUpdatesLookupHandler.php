<?php

namespace App\Features;

use App\Dto\UserUpdatesLookupCommand;
use App\Http\Resources\V4\ProfileLastUpdatesResource;
use App\Support\CachedData;
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

    protected function resource(CachedData $results): JsonResource
    {
        return new ProfileLastUpdatesResource($results);
    }
}
