<?php

namespace App\Features;

use App\Dto\AnimeRelationsLookupCommand;
use App\Http\Resources\V4\AnimeRelationsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeRelationsLookupCommand, JsonResponse>
 */
final class AnimeRelationsLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        return new AnimeRelationsResource(
            $results->first()
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeRelationsLookupCommand::class;
    }
}
