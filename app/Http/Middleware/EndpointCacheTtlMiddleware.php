<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use App\Support\CacheOptions;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware which sets the cache ttl globally based on the endpoint's name
 */
final class EndpointCacheTtlMiddleware
{
    // CacheOptions instance is singleton, so we set the ttl globally
    public function __construct(private readonly CacheOptions $cacheOptions,
                                private readonly JikanConfig $jikanConfig)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $routeName = HttpHelper::getRouteName($request);
        $ttl = $this->jikanConfig->cacheTtlForEndpoint($routeName);
        $this->cacheOptions->setTtl($ttl);

        $response = $next($request);

        $this->cacheOptions->setTtl(null);
        return $response;
    }
}
