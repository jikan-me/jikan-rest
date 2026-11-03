<?php

namespace Tests\Unit;

use App\Contracts\Mediator;
use App\Contracts\UnitOfWork;
use Tests\TestCase;

/**
 * Octane runs each request in a clone of the long-lived base application.
 * Anything the mediator's handler factories capture (repositories and their
 * cached query builders) must therefore be resolved inside that clone, never
 * on the base app, or filters leak from one request into the next.
 */
final class OctaneStateIsolationTest extends TestCase
{
    public function test_building_the_mediator_in_a_sandbox_does_not_resolve_the_factory_map_on_the_base_app(): void
    {
        $sandbox = clone $this->app;
        $sandbox->make(Mediator::class);

        $this->assertFalse($this->app->resolved('jikan-mediator-handler-factories'));
    }

    public function test_each_sandbox_gets_its_own_handler_factories(): void
    {
        $first = $this->handlerFactories(clone $this->app);
        $second = $this->handlerFactories(clone $this->app);

        $this->assertNotEmpty($first);
        $this->assertSame(array_keys($first), array_keys($second));

        $key = array_key_first($first);
        $this->assertNotSame($first[$key], $second[$key], 'factory closures (and the repositories they capture) are shared between sandboxes');
    }

    public function test_repository_query_builders_are_not_shared_between_calls(): void
    {
        $repository = $this->app->make(UnitOfWork::class)->anime();

        $filtered = $repository->exceptItemsWithAdultRating();
        $fresh = $repository->exceptItemsWithAdultRating();

        // Both calls apply the same single where-clause; if the builder were
        // shared, the second would carry the first's clauses as well.
        $this->assertNotSame($filtered, $fresh);
        $this->assertCount(
            count($filtered->getQuery()->wheres),
            $fresh->getQuery()->wheres
        );
    }

    /**
     * @return array<string, \Closure>
     */
    private function handlerFactories($app): array
    {
        $mediator = $app->make(Mediator::class);
        $property = new \ReflectionProperty($mediator, 'handlerFactories');

        return $property->getValue($mediator);
    }
}
