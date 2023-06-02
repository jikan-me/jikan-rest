<?php

namespace App\Features;

use App\Dto\UserStatisticsLookupCommand;
use App\Http\Resources\V4\ProfileStatisticsResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserStatisticsLookupCommand>
 */
final class UserStatisticsLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserStatisticsLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ProfileStatisticsResource($results);
    }
}
