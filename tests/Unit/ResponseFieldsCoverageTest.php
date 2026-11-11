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

    public function test_every_openapi_operation_documents_the_fields_parameter(): void
    {
        $spec = json_decode((string) file_get_contents(base_path('storage/api-docs/api-docs.json')), true);
        $missing = [];

        foreach ($spec['paths'] as $path => $operations) {
            foreach ($operations as $method => $operation) {
                if (!in_array($method, ['get', 'post', 'put', 'patch', 'delete'], true)) {
                    continue;
                }

                $parameters = collect($operation['parameters'] ?? [])->pluck('name')->filter()->all();
                if (!in_array('fields', $parameters, true)) {
                    $missing[] = strtoupper($method).' '.$path;
                }
            }
        }

        $this->assertSame([], $missing, 'OpenAPI operations missing fields: '.implode(', ', $missing));
    }
}
