<?php

namespace App\Features;

use App\Dto\PersonMangaLookupCommand;
use App\Http\Resources\V4\PersonMangaCollection;
use App\Support\CachedData;
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

    protected function resource(CachedData $results): JsonResource
    {
        return new PersonMangaCollection($results->get("published_manga"));
    }
}
