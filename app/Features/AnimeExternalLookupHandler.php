<?php

namespace App\Features;

use App\Dto\AnimeExternalLookupCommand;
use App\Http\Resources\V4\ExternalLinksResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeExternalLookupCommand, JsonResponse>
 */
final class AnimeExternalLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new ExternalLinksResource(
            $results
        );
    }

    public function requestClass(): string
    {
        return AnimeExternalLookupCommand::class;
    }
}
