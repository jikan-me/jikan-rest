<?php

namespace App\Features;

use App\Concerns\ScraperResultCache;
use App\Contracts\AnimeRepository;
use App\Dto\QueryFullAnimeCommand;
use App\Http\Resources\V4\AnimeFullResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;


/**
 * @extends ItemLookupHandler<QueryFullAnimeCommand, JsonResponse>
 */
final class QueryFullAnimeHandler extends ItemLookupHandler
{
    public function __construct(AnimeRepository $repository)
    {
        parent::__construct($repository);
    }

    public function requestClass(): string
    {
        return QueryFullAnimeCommand::class;
    }

    protected function resource(Collection $results): JsonResource
    {
        return new AnimeFullResource($results->first());
    }
}
