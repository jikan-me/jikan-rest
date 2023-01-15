<?php

namespace App\Features;

use App\Dto\AnimeExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeExternalLookupCommand, JsonResponse>
 */
final class AnimeExternalLookupHandler extends ItemLookupHandler
{
    protected function resource(Collection $results): JsonResource
    {
        return new ExternalLinksResource(
            $results->first()
        );
    }

    public function requestClass(): string
    {
        return AnimeExternalLookupCommand::class;
    }
}
