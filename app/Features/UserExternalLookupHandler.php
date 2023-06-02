<?php

namespace App\Features;

use App\Dto\UserExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
use App\Support\CachedData;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends UserLookupHandler<UserExternalLookupCommand>
 */
final class UserExternalLookupHandler extends UserLookupHandler
{
    public function requestClass(): string
    {
        return UserExternalLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ExternalLinksResource($results);
    }
}
