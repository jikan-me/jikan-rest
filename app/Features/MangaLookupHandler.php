<?php

namespace App\Features;

use App\Dto\MangaLookupCommand;
use App\Http\Resources\V4\MangaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<MangaLookupCommand, JsonResponse>
 */
final class MangaLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return MangaLookupCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new MangaResource($results->first());
    }
}
