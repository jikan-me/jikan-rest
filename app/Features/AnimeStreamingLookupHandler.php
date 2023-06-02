<?php

namespace App\Features;

use App\Dto\AnimeStreamingLookupCommand;
use App\Http\Resources\V4\StreamingLinksResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeStreamingLookupCommand, JsonResponse>
 */
final class AnimeStreamingLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        return new StreamingLinksResource(
            $results
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeStreamingLookupCommand::class;
    }
}
