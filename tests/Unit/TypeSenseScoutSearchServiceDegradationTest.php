<?php

namespace Tests\Unit;

use App\Contracts\Repository;
use App\Contracts\SearchAnalyticsService;
use App\Services\TypesenseCollectionDescriptor;
use App\Services\TypeSenseScoutSearchService;
use App\Support\JikanConfig;
use App\Support\SearchEngineFailure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Exceptions;
use Laravel\Scout\Builder;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Typesense\Documents;
use Typesense\Exceptions\ConfigError;
use Typesense\Exceptions\HTTPStatus0Error;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\ObjectUnprocessable;
use Typesense\Exceptions\RequestMalformed;
use Typesense\Exceptions\RequestUnauthorized;
use Typesense\Exceptions\ServerError;
use Typesense\Exceptions\Timeout;

final class TypeSenseScoutSearchServiceDegradationTest extends TestCase
{
    /**
     * Builds the service and captures the search closure it hands to the repository.
     */
    private function middleware(): \Closure
    {
        $captured = null;
        /** @var Repository&MockInterface $repository */
        $repository = \Mockery::mock(Repository::class);
        $repository->allows('search')->andReturnUsing(function ($q, $closure) use (&$captured) {
            $captured = $closure;

            return \Mockery::mock(Builder::class);
        });
        $repository->allows('createEntity')->andReturn(new \stdClass);

        $analytics = \Mockery::mock(SearchAnalyticsService::class);
        $analytics->allows('logSearch');

        $service = new TypeSenseScoutSearchService(
            $repository,
            $this->app->make(JikanConfig::class),
            (new \ReflectionClass(TypesenseCollectionDescriptor::class))->newInstanceWithoutConstructor(),
            $analytics
        );
        $service->search('naruto');

        return $captured;
    }

    private function documentsThrowing(\Throwable $e): Documents
    {
        $documents = \Mockery::mock(Documents::class);
        $documents->allows('search')->andThrow($e);

        return $documents;
    }

    public function testMissingCollectionDegradesToEmptyResult(): void
    {
        $result = ($this->middleware())($this->documentsThrowing(new ObjectNotFound('no collection')), 'naruto', ['per_page' => 25]);

        $this->assertSame([], $result['hits']);
        $this->assertSame(0, $result['found']);
    }

    public function testUnreachableEngineDegradesToEmptyResult(): void
    {
        $result = ($this->middleware())($this->documentsThrowing(new HTTPStatus0Error('conn refused')), 'naruto', []);

        $this->assertSame([], $result['hits']);
    }

    public function testUnauthorizedIsReportedAndPropagates(): void
    {
        Exceptions::fake();

        try {
            ($this->middleware())($this->documentsThrowing(new RequestUnauthorized('bad key')), 'naruto', []);
            $this->fail('Expected the auth failure to propagate.');
        } catch (RequestUnauthorized) {
            Exceptions::assertReported(RequestUnauthorized::class);
        }
    }

    public function testConfigErrorPropagates(): void
    {
        $this->expectException(ConfigError::class);

        ($this->middleware())($this->documentsThrowing(new ConfigError('no nodes')), 'naruto', []);
    }

    /**
     * @return array<string, array{\Throwable, bool}>
     */
    public static function classifications(): array
    {
        return [
            'not found' => [new ObjectNotFound, true],
            'server error' => [new ServerError, true],
            'timeout' => [new Timeout, true],
            'status 0' => [new HTTPStatus0Error, true],
            'laravel connection' => [new ConnectionException('x'), true],
            'wrapped' => [new \RuntimeException('w', 0, new ServerError), true],
            'unauthorized' => [new RequestUnauthorized, false],
            'config' => [new ConfigError, false],
            'code 403' => [new \RuntimeException('forbidden', 403), false],
            'malformed' => [new RequestMalformed, false],
            'unprocessable' => [new ObjectUnprocessable, false],
            'logic' => [new \LogicException, false],
        ];
    }

    #[DataProvider('classifications')]
    public function testClassification(\Throwable $e, bool $degradable): void
    {
        $this->assertSame($degradable, SearchEngineFailure::isDegradable($e));
    }
}
