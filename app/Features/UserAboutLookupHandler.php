<?php

namespace App\Features;

use App\Dto\UserAboutLookupCommand;
use App\Http\Resources\V4\ProfileAboutResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserAboutLookupCommand>
 */
final class UserAboutLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserAboutLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ProfileAboutResource($results);
    }
}
