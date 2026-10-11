<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Removes the self-served Swagger UI endpoint `/v4/api/documentation` from the
 * public API surface (product decision: keep l5-swagger + spec generation, but
 * don't expose the UI viewer under /v4).
 *
 * Runs before routing, so it takes effect regardless of l5-swagger's own route
 * registration order. All other Swagger routes (e.g. `v4/api-docs`) are served
 * as before.
 */
final class DisableSwaggerUi
{
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        if ($request->path() === 'v4/api/documentation') {
            abort(404);
        }

        return $next($request);
    }
}