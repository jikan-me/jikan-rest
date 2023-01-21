<?php

namespace App\Features;

use App\Dto\PersonVoicesLookupCommand;
use App\Http\Resources\V4\PersonVoicesCollection;
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

    protected function resource(Collection $results): JsonResource
    {
        return new PersonVoicesCollection($results->offsetGetFirst("voice_acting_roles"));
    }
}
