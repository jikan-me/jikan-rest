<?php

namespace App\Features;

use App\Dto\MangaSearchCommand;
use App\Http\Resources\V4\MangaCollection;
use App\Http\Resources\V4\MangaResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * @extends SearchRequestHandler<MangaSearchCommand, MangaCollection>
 */
class MangaSearchHandler extends SearchRequestHandler
{
    /**
     * {@inheritDoc}
     */
    public function requestClass(): string
    {
        return MangaSearchCommand::class;
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     */
    protected function renderResponse(LengthAwarePaginator $paginator): MangaCollection
    {
        return new MangaCollection($paginator);
    }

    /**
     * @param  Collection<array-key, mixed>  $items
     */
    protected function renderBatchResponse(Collection $items): JsonResponse
    {
        return new JsonResponse(['data' => MangaResource::collection($items)->resolve()]);
    }
}
