<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * Guards config('jikan.per_endpoint_cache_ttl') against the registered v4
 * route table. Adding a controller route without a TTL entry (or leaving a
 * stale/renamed entry behind) fails the suite instead of silently falling
 * back to CACHE_DEFAULT_EXPIRE. Entries are keyed by route name.
 */
final class CacheTtlMapTest extends TestCase
{
    public function test_every_registered_v4_controller_route_has_a_cache_ttl_entry(): void
    {
        $missing = array_keys(array_diff_key(
            $this->registeredV4RouteNames(),
            config('jikan.per_endpoint_cache_ttl', [])
        ));

        sort($missing);

        $this->assertSame([], $missing, 'v4 routes missing per_endpoint_cache_ttl entries: '.implode(', ', $missing));
    }

    public function test_every_cache_ttl_entry_is_backed_by_a_registered_v4_route(): void
    {
        $stale = array_keys(array_diff_key(
            config('jikan.per_endpoint_cache_ttl', []),
            $this->registeredV4RouteNames()
        ));

        sort($stale);

        $this->assertSame([], $stale, 'per_endpoint_cache_ttl entries with no registered v4 route (dead keys): '.implode(', ', $stale));
    }

    public function test_every_v4_controller_route_is_named(): void
    {
        $unnamed = [];
        foreach ($this->v4ControllerRoutes() as $route) {
            if ((string) $route->getName() === '') {
                $unnamed[] = $route->uri();
            }
        }

        $this->assertSame([], $unnamed, 'v4 controller routes without a name: '.implode(', ', $unnamed));
    }

    /**
     * @return array<string, bool> route name => true for every v4 controller route
     */
    private function registeredV4RouteNames(): array
    {
        $names = [];
        foreach ($this->v4ControllerRoutes() as $route) {
            $names[(string) $route->getName()] = true;
        }

        return $names;
    }

    /**
     * @return list<Route>
     */
    private function v4ControllerRoutes(): array
    {
        $routes = [];
        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            /** @var Route $route */
            if (!str_starts_with($route->uri(), 'v4')) {
                continue;
            }

            $controller = $route->getControllerClass();
            if ($controller === null || !str_contains($controller, 'V4DB')) {
                continue;
            }

            $routes[] = $route;
        }

        return $routes;
    }
}
