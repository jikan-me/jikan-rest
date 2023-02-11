<?php

namespace App\Features;

use App\Dto\AnimeLookupCommand;
use App\Http\Resources\V4\AnimeResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeLookupCommand, JsonResponse>
 */
final class AnimeLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new AnimeResource($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeLookupCommand::class;
    }
}
