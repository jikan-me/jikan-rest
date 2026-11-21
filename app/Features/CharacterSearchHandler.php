<?php

namespace App\Features;

use App\Dto\CharactersSearchCommand;
use App\Http\Resources\V4\CharacterCollection;
use App\Http\Resources\V4\CharacterResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * @extends SearchRequestHandler<CharactersSearchCommand, CharacterCollection>
 */
final class CharacterSearchHandler extends SearchRequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return CharactersSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): CharacterCollection
    {
        return new CharacterCollection($paginator);
    }

    /**
     * @param  Collection<array-key, mixed>  $items
     */
    protected function renderBatchResponse(Collection $items): JsonResponse
    {
        return new JsonResponse(['data' => CharacterResource::collection($items)->resolve()]);
    }
}
