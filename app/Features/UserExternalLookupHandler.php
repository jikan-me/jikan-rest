<?php

namespace App\Features;

use App\Dto\UserExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
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

    protected function resource(Collection $results): JsonResource
    {
        return new ExternalLinksResource($results->first());
    }
}
