<?php

namespace App\Features;

use App\Dto\CharacterAnimeLookupCommand;
use App\Http\Resources\V4\CharacterAnimeCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterAnimeLookupCommand, JsonResponse>
 */
final class CharacterAnimeLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return new CharacterAnimeCollection(
            $results->offsetGetFirst("animeography")
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterAnimeLookupCommand::class;
    }
}
