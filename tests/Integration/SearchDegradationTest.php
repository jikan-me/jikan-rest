<?php

namespace Tests\Integration;

use App\Contracts\DataRequest;
use App\Contracts\Mediator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\RequestUnauthorized;
use Typesense\Exceptions\ServiceUnavailable;

/**
 * Engine-less: the mediator is replaced by one that throws, so no Typesense
 * server is needed. Only search-engine outages degrade to an empty 200; every
 * other failure (404, auth/config, bugs) must keep its real status.
 */
final class SearchDegradationTest extends TestCase
{
    private function mediatorThrowing(\Throwable $e): void
    {
        $this->app->instance(Mediator::class, new class($e) implements Mediator
        {
            public function __construct(private readonly \Throwable $e) {}

            public function send(DataRequest $requestData)
            {
                throw $this->e;
            }
        });
    }

    public function testMissingCollectionDegradesToEmpty200(): void
    {
        $this->mediatorThrowing(new ObjectNotFound('Collection not found'));

        $this->get('/v4/producers')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('pagination.items.count', 0);
    }

    public function testUnreachableEngineDegradesToEmpty200(): void
    {
        $this->mediatorThrowing(new ServiceUnavailable('down'));

        $this->get('/v4/producers')->assertOk()->assertJsonPath('data', []);
    }

    public function testNotFoundIsNotSwallowed(): void
    {
        $this->mediatorThrowing(new NotFoundHttpException('User not found'));

        $this->get('/v4/users/userbyid/1')->assertStatus(404);
    }

    public function testAuthErrorPropagatesAsServerError(): void
    {
        $this->mediatorThrowing(new RequestUnauthorized('Forbidden - a valid `x-typesense-api-key` header must be sent.'));

        $this->get('/v4/producers')->assertStatus(500);
    }

    public function testUnexpectedErrorPropagates(): void
    {
        $this->mediatorThrowing(new \LogicException('bug'));

        $this->get('/v4/producers')->assertStatus(500);
    }
}
