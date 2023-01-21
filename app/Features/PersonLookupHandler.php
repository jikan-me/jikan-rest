<?php

namespace App\Features;

use App\Dto\PersonLookupCommand;
use App\Http\Resources\V4\PersonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<PersonLookupCommand, JsonResponse>
 */
final class PersonLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return PersonLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new PersonResource($results->first());
    }
}
