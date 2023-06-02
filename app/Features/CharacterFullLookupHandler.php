<?php

namespace App\Features;

use App\Dto\CharacterFullLookupCommand;
use App\Http\Resources\V4\CharacterFullResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterFullLookupCommand, JsonResponse>
 */
final class CharacterFullLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new CharacterFullResource($results);
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterFullLookupCommand::class;
    }
}
