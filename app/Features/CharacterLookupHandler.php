<?php

namespace App\Features;

use App\Dto\CharacterLookupCommand;
use App\Http\Resources\V4\CharacterResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterLookupCommand, JsonResponse>
 */
final class CharacterLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new CharacterResource($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterLookupCommand::class;
    }
}
