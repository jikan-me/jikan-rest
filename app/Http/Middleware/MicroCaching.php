<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Support\Facades\Cache;

class MicroCaching
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if ($request->header('auth') === env('APP_KEY')) {
            return $next($request);
        }

        if (!env('CACHING')) {
            return $next($request);
        }

        // Microcaching should not work alongside redis caching
        if (!env('MICROCACHING', false) || env('CACHE_DRIVER', 'file') === 'redis') {
            return $next($request);
        }

        $fingerprint = "microcache:".HttpHelper::resolveRequestFingerprint($request);
        if (Cache::has($fingerprint)) {
            return response()
                ->json(
                    json_decode(Cache::get($fingerprint), true)
                );
        }

        return $next($request);
    }

    public static function setMicroCache($fingerprint, $cache) {
        $fingerprint = "microcache:".$fingerprint;
        $cache = json_encode($cache);

        Cache::add($fingerprint, $cache, env('MICROCACHING_EXPIRE', 5));
    }
}
