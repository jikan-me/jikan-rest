<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Master declared `anime/{id:[0-9]+}` (etc.), so a non-numeric id was a 404.
 * The Laravel port must keep that: the `id` constraint has to be on every
 * route inside those groups (not merely on a discarded registrar).
 */
class RouteConstraintsTest extends TestCase
{
    private const RESOURCES = ['anime', 'manga', 'characters', 'people', 'producers'];

    public function testEveryRouteUnderAnIdPrefixConstrainsIdToDigits(): void
    {
        $checked = 0;
        foreach (RouteFacade::getRoutes() as $route) {
            /** @var Route $route */
            foreach (self::RESOURCES as $resource) {
                if (!str_starts_with($route->uri(), "v4/$resource/{id}")) {
                    continue;
                }
                $checked++;
                $this->assertSame(
                    '[0-9]+',
                    $route->wheres['id'] ?? null,
                    "Route {$route->uri()} has no numeric id constraint"
                );
            }
        }

        $this->assertGreaterThanOrEqual(40, $checked, 'expected to inspect all id-prefixed v4 routes');
    }

    #[DataProvider('nonNumericIdProvider')]
    public function testNonNumericIdIsNotFound(string $uri): void
    {
        $this->get($uri)
            ->assertStatus(404)
            ->assertJsonPath('type', 'HttpException');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function nonNumericIdProvider(): array
    {
        $cases = [];
        foreach (self::RESOURCES as $resource) {
            $cases["$resource: letters"] = ["/v4/$resource/abc"];
            $cases["$resource: sub-resource"] = ["/v4/$resource/abc/".($resource === 'producers' ? 'full' : 'pictures')];
        }

        return $cases;
    }
}
