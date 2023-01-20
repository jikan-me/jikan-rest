<?php

namespace App\Features;

use App\Dto\CharacterFullLookupCommand;
use App\Http\Resources\V4\CharacterFullResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterFullLookupCommand, JsonResponse>
 */
final class CharacterFullLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        return new CharacterFullResource($results->first());
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterFullLookupCommand::class;
    }
}
