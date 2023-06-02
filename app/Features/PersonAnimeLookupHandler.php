<?php

namespace App\Features;

use App\Dto\PersonAnimeLookupCommand;
use App\Http\Resources\V4\PersonAnimeCollection;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<PersonAnimeLookupCommand, JsonResponse>
 */
final class PersonAnimeLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return PersonAnimeLookupCommand::class;
    }

    protected function resource(CachedData $results): PersonAnimeCollection
    {
        return new PersonAnimeCollection($results->get("anime_staff_positions"));
    }
}
