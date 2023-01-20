<?php

namespace App\Features;

use App\Dto\CharacterVoicesLookupCommand;
use App\Http\Resources\V4\CharacterSeiyuuCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterVoicesLookupCommand, JsonResponse>
 */
final class CharacterVoicesLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return new CharacterSeiyuuCollection(
            $results->offsetGetFirst("voice_actors")
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterVoicesLookupCommand::class;
    }
}
