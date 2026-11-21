<?php

namespace App\Features;

use App\Dto\AnimeSearchCommand;
use App\Http\Resources\V4\AnimeCollection;
use App\Http\Resources\V4\AnimeResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * @extends SearchRequestHandler<AnimeSearchCommand, AnimeCollection>
 */
class AnimeSearchHandler extends SearchRequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return AnimeSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): AnimeCollection
    {
        return new AnimeCollection($paginator);
    }

    /**
     * @param  Collection<array-key, mixed>  $items
     */
    protected function renderBatchResponse(Collection $items): JsonResponse
    {
        return new JsonResponse(['data' => AnimeResource::collection($items)->resolve()]);
    }
}
