<?php

namespace App\Features;

use App\Dto\UserFavoritesLookupCommand;
use App\Http\Resources\V4\ProfileFavoritesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserFavoritesLookupCommand>
 */
final class UserFavoritesLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserFavoritesLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ProfileFavoritesResource($results->first());
    }
}
