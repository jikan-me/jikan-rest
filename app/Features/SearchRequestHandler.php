<?php

namespace App\Features;

use App\Contracts\DataRequest;
use App\Contracts\RequestHandler;
use App\Services\QueryBuilderService;
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
            // A missing/unreachable search (Typesense) collection must render
            // an EMPTY result, never 500 or 404 — search failures degrade
            // gracefully for the whole search surface.
            try {
                report($e);
            } catch (\Throwable $ignore) {
            }
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
            // Last-resort: rendering must not take a search endpoint down.
            // Render an empty v4 envelope instead.
            try {
                report($e);
            } catch (\Throwable $ignore) {
            }
            $perPage = (int) ($limit ?? 25);

            return new JsonResponse([
                'data' => [],
                'pagination' => [
                    'last_visible_page' => 1,
                    'has_next_page' => false,
                    'current_page' => 1,
                    'items' => ['count' => 0, 'total' => 0, 'per_page' => $perPage],
                ],
            ]);
        }
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
