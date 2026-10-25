<?php

namespace App\Features;

use App\Contracts\DataRequest;
use App\Contracts\RequestHandler;
use App\Services\QueryBuilderService;
use App\Support\SearchEngineFailure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * @template TRequest of DataRequest
 * @template TResponse of ResourceCollection|JsonResource|JsonResponse|Response
 *
 * @implements RequestHandler<TRequest, TResponse>
 */
abstract class SearchRequestHandler implements RequestHandler
{
    public function __construct(
        private readonly QueryBuilderService $queryBuilderService
    ) {}

    /**
     * {@inheritDoc}
     */
    public function handle($request)
    {
        // note: ->all() doesn't transform the dto, all the parsed data is returned as it was parsed. (and validated)
        $requestData = collect($request->all());
        $prohibitedSearchCharacters = collect(["\n", '\\n', "\r", "\t", "\0", '%0A']);
        if (in_array($requestData->get('q', ''), $prohibitedSearchCharacters->toArray())
            || $prohibitedSearchCharacters->filter(fn ($value) => strpos($requestData->get('q', ''), $value) !== false)->count() > 0) {
            throw ValidationException::withMessages([
                'q' => 'The q parameter cannot contain any of the following characters: \\n, \\r, \\t, \\0, %0A',
            ]);
        }
        $page = $requestData->get('page');
        $limit = $requestData->get('limit', (int) config('jikan.max_results_per_page', 25));

        try {
            $builder = $this->queryBuilderService->query(
                $this->prepareOrderByParam($requestData)
            );
            $paginator = $this->queryBuilderService->paginateBuilder($builder, $page, $limit);
        } catch (\Throwable $e) {
            // Only a search-engine outage (collection missing, engine
            // unreachable, timeout, 5xx) degrades to an empty result; Mongo
            // errors, validation and bugs propagate (same rule as
            // SearchController).
            $this->reportDegradableOrRethrow($e);
            $paginator = new ConcreteLengthAwarePaginator(
                collect(),
                0,
                (int) ($limit ?? 25),
                (int) ($page ?? 1),
                ['path' => app('request')->fullUrl()]
            );
        }

        try {
            return $this->renderResponse($paginator);
        } catch (\Throwable $e) {
            // Rendering can still reach the engine (lazy hydration); only that
            // kind of failure gets the empty v4 envelope.
            $this->reportDegradableOrRethrow($e);

            return new JsonResponse([
                'data' => [],
                'pagination' => [
                    'last_visible_page' => 1,
                    'has_next_page' => false,
                    'current_page' => 1,
                    'items' => ['count' => 0, 'total' => 0, 'per_page' => (int) ($limit ?? 25)],
                ],
            ]);
        }
    }

    /**
     * Reports a degradable search-engine failure, rethrows anything else.
     *
     * @throws \Throwable
     */
    private function reportDegradableOrRethrow(\Throwable $e): void
    {
        if (! SearchEngineFailure::isDegradable($e)) {
            throw $e;
        }

        report($e);
    }

    /**
     * @param  LengthAwarePaginator<int, mixed>  $paginator
     * @return TResponse
     */
    abstract protected function renderResponse(LengthAwarePaginator $paginator);

    /**
     * @param  Collection<array-key, mixed>  $requestData
     * @return Collection<array-key, mixed>
     */
    protected function prepareOrderByParam(Collection $requestData): Collection
    {
        if ($requestData->has('order_by') && ! is_null($requestData->get('order_by'))) {
            $requestData->offsetSet('order_by', $requestData->get('order_by')->label);

            return $requestData;
        }

        return $requestData;
    }
}
