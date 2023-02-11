<?php

namespace App\Features;

use App\Dto\CharacterMangaLookupCommand;
use App\Http\Resources\V4\CharacterMangaCollection;
use App\Support\CachedData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @extends ItemLookupHandler<CharacterMangaLookupCommand, JsonResponse>
 */
final class CharacterMangaLookupHandler extends ItemLookupHandler
{
    protected function resource(CachedData $results): JsonResource
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return new CharacterMangaCollection(
            $results->get("mangaography")
        );
    }

    /**
     * @inheritDoc
     */
    public function requestClass(): string
    {
        return CharacterMangaLookupCommand::class;
    }
}
