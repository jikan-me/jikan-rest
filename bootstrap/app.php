<?php

use App\Http\Middleware\DisableSwaggerUi;
use App\Http\Middleware\EndpointCacheTtlMiddleware;
use App\Http\Middleware\GzipResponse;
use App\Http\Middleware\MicroCaching;
use App\Http\Middleware\SourceHeartbeatMonitor;
use Composer\InstalledVersions;
use Illuminate\Foundation\Application;

// Workload needs more than the 128M CLI default: large catalog searches hydrate
// >250-hit chunked pages (e.g. `?q=*` on ~10k indexed anime) and OOM otherwise.
ini_set('memory_limit', (string) (getenv('PHP_MEMORY_LIMIT') ?: '512M'));
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
        // Trust the reverse proxy / load balancer in front of the app so that
        // request()->ip() (used by the rate limiter) is the real client address.
        // Comma-separated IPs/CIDRs, or `*` to trust every proxy. This closure
        // runs before the config repository exists, so env() is correct here.
        $trustedProxies = env('TRUSTED_PROXIES');
        if (is_string($trustedProxies) && trim($trustedProxies) !== '') {
            $middleware->trustProxies(
                at: trim($trustedProxies) === '*'
                    ? '*'
                    : array_map('trim', explode(',', $trustedProxies))
            );
        }

        // CORS is handled by Laravel's default global HandleCors middleware
        // (config/cors.php), enabled only when CORS_MIDDLEWARE=true; the legacy
        // custom CorsMiddleware was removed.
        // Don't expose the self-served Swagger UI under /v4 (product decision);
        // l5-swagger + spec generation stay. See DisableSwaggerUi.
        $middleware->append(DisableSwaggerUi::class);

        $middleware->alias([
            'microcaching' => MicroCaching::class,
            'gzip' => GzipResponse::class,
            'source-health-monitor' => SourceHeartbeatMonitor::class,
            'cache-ttl' => EndpointCacheTtlMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Rendering is handled by App\Exceptions\Handler, bound in AppServiceProvider.
    })
    ->create();
