<?php

namespace App\Features;

use App\Dto\PersonMangaLookupCommand;
use App\Http\Resources\V4\PersonMangaCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<PersonMangaLookupCommand, JsonResponse>
 */
final class PersonMangaLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return PersonMangaLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new PersonMangaCollection($results->offsetGetFirst("published_manga"));
    }
}
