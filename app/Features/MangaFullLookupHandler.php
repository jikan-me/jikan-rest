<?php

namespace App\Features;

use App\Dto\MangaFullLookupCommand;
use App\Http\Resources\V4\MangaFullResource;
use App\Support\CachedData;
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

    protected function resource(CachedData $results): JsonResource
    {
        return new MangaFullResource($results);
    }
}
