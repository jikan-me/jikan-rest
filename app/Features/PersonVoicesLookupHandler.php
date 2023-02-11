<?php

namespace App\Features;

use App\Dto\PersonVoicesLookupCommand;
use App\Http\Resources\V4\PersonVoicesCollection;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<PersonVoicesLookupCommand, JsonResponse>
 */
final class PersonVoicesLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return PersonVoicesLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new PersonVoicesCollection($results->get("voice_acting_roles"));
    }
}
