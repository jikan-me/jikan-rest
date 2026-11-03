<?php

namespace App\Features;

use App\Dto\ClubStaffLookupCommand;
use App\Http\Resources\V4\ClubStaffResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @extends ItemLookupHandler<ClubStaffLookupCommand, JsonResponse>
 */
final class ClubStaffLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new ClubStaffResource($results);
    }

    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return ClubStaffLookupCommand::class;
    }
}
