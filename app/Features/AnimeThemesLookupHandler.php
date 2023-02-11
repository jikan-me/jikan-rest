<?php

namespace App\Features;

use App\Dto\AnimeThemesLookupCommand;
use App\Http\Resources\V4\AnimeThemesResource;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<AnimeThemesLookupCommand, JsonResponse>
 */
final class AnimeThemesLookupHandler extends ItemLookupHandler
{

    protected function resource(CachedData $results): JsonResource
    {
        return new AnimeThemesResource(
            $results
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return AnimeThemesLookupCommand::class;
    }
}
