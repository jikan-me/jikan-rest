<?php

namespace App\Features;

use App\Dto\AnimeLookupCommand;
use App\Http\Resources\V4\AnimeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeLookupCommand, JsonResponse>
 */
final class QueryAnimeHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeLookupCommand::class;
    }
}
