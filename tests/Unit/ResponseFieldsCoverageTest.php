<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ResponseFieldsCoverageTest extends TestCase
{
    public function test_every_named_v4_route_carries_the_fields_middleware(): void
    {
        $missing = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = (string) $route->getName();

            if (!str_starts_with($name, 'v4.')) {
                continue;
            }

            if (!in_array('select-fields', $route->gatherMiddleware(), true)) {
                $missing[] = $name;
            }
        }

        $this->assertSame([], $missing, 'Routes missing select-fields: '.implode(', ', $missing));
    }
}
