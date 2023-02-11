<?php

namespace App\Features;

use App\Dto\ProducerLookupCommand;
use App\Http\Resources\V4\ProducerResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<ProducerLookupCommand, JsonResponse>
 */
final class ProducerLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return ProducerLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ProducerResource($results);
    }
}
