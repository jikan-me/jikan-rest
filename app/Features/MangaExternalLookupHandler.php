<?php

namespace App\Features;

use App\Dto\MangaExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<MangaExternalLookupCommand, JsonResponse>
 */
final class MangaExternalLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return MangaExternalLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new ExternalLinksResource($results->first());
    }
}
