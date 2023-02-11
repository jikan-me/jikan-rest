<?php

namespace App\Features;

use App\Dto\ClubLookupCommand;
use App\Http\Resources\V4\ClubResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<ClubLookupCommand, JsonResponse>
 */
final class ClubLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new ClubResource($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return ClubLookupCommand::class;
    }
}
