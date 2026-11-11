<?php

namespace Tests\Unit;

use App\Anime;
use App\Contracts\DataRequest;
use App\Features\SearchRequestHandler;
use App\Services\QueryBuilderService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use MongoDB\Driver\Exception\ConnectionTimeoutException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\RequestUnauthorized;
use Typesense\Exceptions\ServiceUnavailable;

/**
 * SearchRequestHandler (the /v4/{anime,manga,characters,...} search handlers)
 * degrades to an empty 200 only for search-engine outages; everything else
 * propagates so it renders as the real error (500 for bugs/Mongo errors).
 */
final class SearchRequestHandlerDegradationTest extends TestCase
{
    /**
     * Swaps the exception handler for one that records report() calls.
     *
     * @return \ArrayObject<int, \Throwable>
     */
    private function captureReports(): \ArrayObject
    {
        $reported = new \ArrayObject;
        $handler = \Mockery::mock(ExceptionHandler::class);
        $handler->shouldReceive('report')->andReturnUsing(fn (\Throwable $e) => $reported->append($e));
        $this->app->instance(ExceptionHandler::class, $handler);

        return $reported;
    }

    private function queryBuilderThrowing(?\Throwable $e): QueryBuilderService
    {
        return new class($e) implements QueryBuilderService
        {
            public function __construct(private readonly ?\Throwable $e) {}

            public function query(Collection $requestParameters): Builder|EloquentBuilder
            {
                if ($this->e !== null) {
                    throw $this->e;
                }

                return Anime::query();
            }

            public function paginate(EloquentBuilder|Builder $builder, ?int $page = null, ?int $limit = null): array
            {
                return [];
            }

            public function paginateBuilder(Builder|EloquentBuilder $builder, ?int $page = null, ?int $limit = null): LengthAwarePaginator
            {
                return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $limit ?? 25, $page ?? 1);
            }
        };
    }

    /**
     * @return SearchRequestHandler<DataRequest<JsonResponse>, JsonResponse>
     */
    private function handler(QueryBuilderService $queryBuilder, ?\Throwable $renderError = null): SearchRequestHandler
    {
        return new class($queryBuilder, $renderError) extends SearchRequestHandler
        {
            public function __construct(QueryBuilderService $queryBuilder, private readonly ?\Throwable $renderError)
            {
                parent::__construct($queryBuilder);
            }

            public function requestClass(): string
            {
                return DataRequest::class;
            }

            protected function renderResponse(LengthAwarePaginator $paginator): JsonResponse
            {
                if ($this->renderError !== null) {
                    throw $this->renderError;
                }

                return new JsonResponse(['data' => $paginator->items(), 'rendered' => true]);
            }
        };
    }

    private function request(): DataRequest
    {
        return new class implements DataRequest
        {
            /** @return array<string, mixed> */
            public function all(): array
            {
                return ['q' => 'naruto', 'page' => 1, 'limit' => 5];
            }
        };
    }

    /** @return array<string, array{\Throwable}> */
    public static function degradable(): array
    {
        return [
            'missing collection' => [new ObjectNotFound('Collection not found')],
            'engine unavailable' => [new ServiceUnavailable('down')],
        ];
    }

    /** @return array<string, array{\Throwable}> */
    public static function notDegradable(): array
    {
        return [
            'bug' => [new \RuntimeException('boom')],
            'mongo' => [new ConnectionTimeoutException('No suitable servers found')],
            'engine auth' => [new RequestUnauthorized('Forbidden')],
        ];
    }

    #[DataProvider('degradable')]
    public function test_engine_outage_while_querying_degrades_to_empty_result(\Throwable $e): void
    {
        $reported = $this->captureReports();

        $response = $this->handler($this->queryBuilderThrowing($e))->handle($this->request());

        $this->assertSame(['data' => [], 'rendered' => true], $response->getData(true));
        $this->assertSame([$e], $reported->getArrayCopy());
    }

    #[DataProvider('notDegradable')]
    public function test_other_failures_while_querying_propagate(\Throwable $e): void
    {
        $this->expectExceptionObject($e);

        $this->handler($this->queryBuilderThrowing($e))->handle($this->request());
    }

    #[DataProvider('degradable')]
    public function test_engine_outage_while_rendering_returns_empty_envelope(\Throwable $e): void
    {
        $reported = $this->captureReports();

        $response = $this->handler($this->queryBuilderThrowing(null), $e)->handle($this->request());

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $response->getData(true)['data']);
        $this->assertSame(0, $response->getData(true)['pagination']['items']['total']);
        $this->assertSame(5, $response->getData(true)['pagination']['items']['per_page']);
        $this->assertSame([$e], $reported->getArrayCopy());
    }

    #[DataProvider('notDegradable')]
    public function test_other_failures_while_rendering_propagate(\Throwable $e): void
    {
        $this->expectExceptionObject($e);

        $this->handler($this->queryBuilderThrowing(null), $e)->handle($this->request());
    }

    public function test_non_degradable_failure_renders_as_500_through_the_exception_handler(): void
    {
        $e = new \RuntimeException('boom');

        try {
            $this->handler($this->queryBuilderThrowing($e))->handle($this->request());
            $this->fail('exception was swallowed');
        } catch (\RuntimeException $caught) {
            $response = app(ExceptionHandler::class)
                ->render(request(), $caught);
            $this->assertSame(500, $response->getStatusCode());
        }
    }
}
