<?php

namespace App\Http\Middleware;

use App\Support\CacheOptions;
use App\Support\HttpHelper;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware which sets the cache ttl globally based on the endpoint's name
 */
final class EndpointCacheTtlMiddleware
{
    // CacheOptions instance is singleton, so we set the ttl globally
    public function __construct(private readonly CacheOptions $cacheOptions,
        private readonly JikanConfig $jikanConfig) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = HttpHelper::getRouteName($request);
        $ttl = $this->jikanConfig->cacheTtlForEndpoint($routeName);
        $this->cacheOptions->setTtl($ttl);

        // Always clear the request-scoped TTL, even when the request throws —
        // otherwise a stale TTL leaks into the next request on the same worker.
        try {
            return $next($request);
        } finally {
            $this->cacheOptions->setTtl(null);
        }
    }
}
