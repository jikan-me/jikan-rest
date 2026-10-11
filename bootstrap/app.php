<?php

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
    \Composer\InstalledVersions::getPrettyVersion('jikan-me/jikan') ?? 'unknown'
);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        using: function () {
            require __DIR__.'/../routes/api.php';
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Gating for these lives inside the middleware classes (config reads
        // are not safe at this bootstrap point — this callback runs before the
        // config repository is bound when the console/http kernel resolves).
        $middleware->append(\App\Http\Middleware\Insights::class);
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);

        $middleware->alias([
            'microcaching' => \App\Http\Middleware\MicroCaching::class,
            'source-health-monitor' => \App\Http\Middleware\SourceHeartbeatMonitor::class,
            'cache-ttl' => \App\Http\Middleware\EndpointCacheTtlMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rendering is handled by App\Exceptions\Handler, bound in AppServiceProvider.
    })
    ->create();
