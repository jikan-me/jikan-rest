<?php

namespace App\Features;

use App\Dto\ClubRelationLookupCommand;
use App\Http\Resources\V4\ClubRelationsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<ClubRelationLookupCommand, JsonResponse>
 */
final class ClubRelationsLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        return new ClubRelationsResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return ClubRelationLookupCommand::class;
    }
}
