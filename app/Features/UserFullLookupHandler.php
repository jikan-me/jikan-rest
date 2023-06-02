<?php

namespace App\Features;

use App\Dto\UserFullLookupCommand;
use App\Http\Resources\V4\ProfileFullResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserFullLookupCommand>
 */
final class UserFullLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserFullLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ProfileFullResource($results);
    }
}
