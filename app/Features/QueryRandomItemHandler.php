<?php

namespace App\Features;

use App\Contracts\Repository;
use App\Contracts\RequestHandler;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

/**
 * @template TRequest of Data
 * @template TResponse of ResourceCollection|JsonResource|Response
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class QueryRandomItemHandler implements RequestHandler
{
    protected function __construct(protected readonly Repository $repository)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($request)
    {
        $results = $this->repository->random();

        if ($results->isEmpty()) {
            // no entries indexed -> 404 instead of a null-crash in the resource
            abort(404);
        }

        return $this->resource($results);
    }

    protected abstract function resource(Collection $results): JsonResource;
}
