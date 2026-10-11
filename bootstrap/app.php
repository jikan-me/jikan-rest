<?php

use App\Http\Middleware\DisableSwaggerUi;
use App\Http\Middleware\EndpointCacheTtlMiddleware;
use App\Http\Middleware\MicroCaching;
use App\Http\Middleware\SourceHeartbeatMonitor;
use Composer\InstalledVersions;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Parser version constant
|--------------------------------------------------------------------------
| Replaces the old PackageVersions\Versions usage (package abandoned).
*/
defined('JIKAN_PARSER_VERSION') or define(
    'JIKAN_PARSER_VERSION',
    InstalledVersions::getPrettyVersion('jikan-me/jikan') ?? 'unknown'
);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
            require __DIR__.'/../routes/api.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // CORS is handled by Laravel's default global HandleCors middleware
        // (config/cors.php); the legacy custom CorsMiddleware was removed.
        // Don't expose the self-served Swagger UI under /v4 (product decision);
        // l5-swagger + spec generation stay. See DisableSwaggerUi.
        $middleware->append(DisableSwaggerUi::class);

        $middleware->alias([
            'microcaching' => MicroCaching::class,
            'source-health-monitor' => SourceHeartbeatMonitor::class,
            'cache-ttl' => EndpointCacheTtlMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rendering is handled by App\Exceptions\Handler, bound in AppServiceProvider.
    })
    ->create();
