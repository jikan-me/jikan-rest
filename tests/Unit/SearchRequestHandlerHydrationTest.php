<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Contracts\DataRequest;
use App\Features\SearchRequestHandler;
use App\Services\QueryBuilderService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder;
use Mockery;
use Tests\TestCase;

final class SearchRequestHandlerHydrationTest extends TestCase
{
    public function test_handler_logs_mismatch_when_engine_has_hits_and_hydration_is_empty(): void
    {
        config(['scout.driver' => 'typesense']);
        Log::spy();

        $handler = new class($this->queryBuilderReturning(24, 0)) extends SearchRequestHandler
        {
            public function requestClass(): string
            {
                return DataRequest::class;
            }

            protected function renderResponse(LengthAwarePaginator $paginator): JsonResponse
            {
                return new JsonResponse(['data' => [], 'pagination' => ['items' => ['total' => $paginator->total()]]]);
            }
        };

        $handler->handle($this->requestData());

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'search.index_mismatch'
                && $context['engine_hits'] === 24
                && $context['hydrated'] === 0);
    }

    public function test_handler_does_not_log_for_the_mongo_text_path(): void
    {
        config(['scout.driver' => 'null']);
        Log::spy();

        $handler = new class($this->queryBuilderReturning(24, 0)) extends SearchRequestHandler
        {
            public function requestClass(): string
            {
                return DataRequest::class;
            }

            protected function renderResponse(LengthAwarePaginator $paginator): JsonResponse
            {
                return new JsonResponse(['data' => []]);
            }
        };

        $handler->handle($this->requestData());

        Log::shouldNotHaveReceived('warning');
    }

    private function queryBuilderReturning(int $total, int $hydrated): QueryBuilderService
    {
        $builder = Mockery::mock(Builder::class);

        $queryBuilder = Mockery::mock(QueryBuilderService::class);
        $queryBuilder->shouldReceive('query')->once()->andReturn($builder);
        $queryBuilder->shouldReceive('paginateBuilder')->once()->andReturn(
            new ConcreteLengthAwarePaginator([], $total, 25, 1)
        );

        return $queryBuilder;
    }

    private function requestData(): object
    {
        return new class
        {
            public function all(): array
            {
                return ['q' => 'naruto', 'page' => 1, 'limit' => 25];
            }
        };
    }
}
