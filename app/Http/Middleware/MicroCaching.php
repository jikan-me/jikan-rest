<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Support\Facades\Redis;

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

        // Microcaching should not work alongside redis caching
        if (!env('MICROCACHING', false) || env('CACHE_DRIVER', 'file') === 'redis') {
            return $next($request);
        }

        $fingerprint = "microcache:".HttpHelper::resolveRequestFingerprint($request);
        if (app('redis')->exists($fingerprint)) {
            return response()
                ->json(
                    json_decode(app('redis')->get($fingerprint), true)
                );
        }

        return $next($request);
    }

    public static function setMicroCache($fingerprint, $cache) {
        $fingerprint = "microcache:".$fingerprint;
        $cache = json_encode($cache);

        app('redis')->set($fingerprint, $cache);
        app('redis')->expire($fingerprint, env('MICROCACHING_EXPIRE', 5));
    }
}
