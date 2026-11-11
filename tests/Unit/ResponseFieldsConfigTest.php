<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ResponseFieldsConfigTest extends TestCase
{
    public function test_every_configured_route_exists_and_maps_to_a_defined_resource(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->keyBy(fn ($route): string => (string) $route->getName());

        foreach (config('response_fields.routes') as $routeName => $resource) {
            $this->assertNotNull($routes->get($routeName), "Configured fields route {$routeName} does not exist");
            $this->assertNotEmpty(config("response_fields.resources.{$resource}"), "Resource {$resource} has no allowlist");
        }
    }

    public function test_the_fields_surface_is_explicitly_scoped(): void
    {
        $this->assertSame([
            'v4.anime.main' => 'anime',
            'v4.search.anime' => 'anime',
            'v4.manga.main' => 'manga',
            'v4.search.manga' => 'manga',
            'v4.character.main' => 'character',
            'v4.search.character' => 'character',
        ], config('response_fields.routes'));
    }
}
