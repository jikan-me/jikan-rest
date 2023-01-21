<?php

namespace App\Features;

use App\Dto\PersonFullLookupCommand;
use App\Http\Resources\V4\PersonFullResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<PersonFullLookupCommand, JsonResponse>
 */
final class PersonFullLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return PersonFullLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new PersonFullResource($results->first());
    }
}
