<?php

namespace App\Features;

use App\Dto\CharacterVoicesLookupCommand;
use App\Http\Resources\V4\CharacterSeiyuuCollection;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterVoicesLookupCommand, JsonResponse>
 */
final class CharacterVoicesLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return new CharacterSeiyuuCollection(
            $results->get("voice_actors")
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
