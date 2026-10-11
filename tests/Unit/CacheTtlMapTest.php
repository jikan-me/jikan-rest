<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * Guards config('jikan.per_endpoint_cache_ttl') against the registered v4
 * route table. Adding a new controller action without a TTL entry (or leaving
 * a stale/renamed entry behind) fails the suite instead of silently falling
 * back to CACHE_DEFAULT_EXPIRE.
 */
final class CacheTtlMapTest extends TestCase
{
    private const V4DB_NAMESPACE = 'App\\Http\\Controllers\\V4DB\\';

    public function test_every_registered_v4_controller_action_has_a_cache_ttl_entry(): void
    {
        $missing = array_values(array_diff_key(
            $this->registeredV4Actions(),
            config('jikan.per_endpoint_cache_ttl', [])
        ));

        sort($missing);

        $this->assertSame([], $missing, 'v4 controller actions missing per_endpoint_cache_ttl entries: '.implode(', ', $missing));
    }

    public function test_every_cache_ttl_entry_is_backed_by_a_registered_v4_route(): void
    {
        $stale = array_values(array_diff_key(
            config('jikan.per_endpoint_cache_ttl', []),
            $this->registeredV4Actions()
        ));

        sort($stale);

        $this->assertSame([], $stale, 'per_endpoint_cache_ttl entries with no registered v4 route (dead keys): '.implode(', ', $stale));
    }

    /**
     * @return array<string, bool> map of "Controller@action" for every v4 route
     *                             whose action lives under App\Http\Controllers\V4DB
     */
    private function registeredV4Actions(): array
    {
        $actions = [];

        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            /** @var Route $route */
            if (! str_starts_with($route->uri(), 'v4')) {
                continue;
            }

            $name = $this->routeName($route);
            if ($name !== null) {
                $actions[$name] = true;
            }
        }

        return $actions;
    }

    /**
     * Mirrors HttpHelper::getRouteName(): last "\" segment of the "uses" value.
     */
    private function routeName(Route $route): ?string
    {
        $uses = $route->getAction('uses');

        if (! is_string($uses) || ! str_starts_with($uses, self::V4DB_NAMESPACE)) {
            return null;
        }

        $parts = explode('\\', $uses);

        return end($parts) ?: null;
    }
}
