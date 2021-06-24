<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Support\Facades\Cache;
use Jikan\Exception\BadResponseException;

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

        if (
            empty($request->segments())
            || !isset($request->segments()[1])
        ) {
            return $next($request);
        }

        if (
            !env('CACHING')
            || !env('MICROCACHING')
            || env('CACHE_DRIVER') !== 'redis'
        ) {
            return $next($request);
        }

        $fingerprint = "microcache:".HttpHelper::resolveRequestFingerprint($request);

        // if cache exists, return cache
        if (app('redis')->exists($fingerprint)) {
            return response()
                ->json(
                   \json_decode(app('redis')->get($fingerprint), true)
                );
        }

        // set cache
        app('redis')->set(
            $fingerprint,
            json_encode(
                $next($request)->getData()
            )
        );

        app('redis')->expire($fingerprint, env('MICROCACHING_EXPIRE', 5));

        return $next($request);
    }

}
