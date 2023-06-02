<?php

namespace App\Features;

use App\Dto\AnimeFullLookupCommand;
use App\Http\Resources\V4\AnimeFullResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


/**
 * @extends ItemLookupHandler<AnimeFullLookupCommand, JsonResponse>
 */
final class AnimeFullLookupHandler extends ItemLookupHandler
{
    public function requestClass(): string
    {
        return AnimeFullLookupCommand::class;
    }

    protected function resource(CachedData $results): JsonResource
    {
        return new AnimeFullResource($results);
    }
}
