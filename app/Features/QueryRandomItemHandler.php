<?php

namespace App\Features;

use App\Contracts\Repository;
use App\Contracts\RequestHandler;
use App\Contracts\DataRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * @template TRequest of DataRequest
 * @template TResponse of ResourceCollection|JsonResource|JsonResponse|Response
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
