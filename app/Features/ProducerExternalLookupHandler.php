<?php

namespace App\Features;

use App\Dto\ProducerExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<ProducerExternalLookupCommand, JsonResponse>
 */
final class ProducerExternalLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return ProducerExternalLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new ExternalLinksResource($results);
    }
}
