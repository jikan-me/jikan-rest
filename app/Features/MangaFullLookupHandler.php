<?php

namespace App\Features;

use App\Dto\MangaFullLookupCommand;
use App\Http\Resources\V4\MangaFullResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<MangaFullLookupCommand, JsonResponse>
 */
final class MangaFullLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return MangaFullLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new MangaFullResource($results->first());
    }
}
