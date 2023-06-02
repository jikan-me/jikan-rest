<?php

namespace App\Features;

use App\Dto\MangaRelationsLookupCommand;
use App\Http\Resources\V4\MangaRelationsResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<MangaRelationsLookupCommand, JsonResponse>
 */
final class MangaRelationsLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return MangaRelationsLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new MangaRelationsResource($results);
    }
}
