<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Support\Facades\Cache;
use Jikan\Exception\BadResponseException;

class MicroCaching
{
    private const NO_CACHING = [
        'RandomController@anime',
        'RandomController@manga',
        'RandomController@characters',
        'RandomController@people',
        'RandomController@users',
        'InsightsController@main'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isset($request->route()[1]['uses'])) {
            $route = explode('\\', $request->route()[1]['uses']);
            $route = end($route);
            if (\in_array($route, self::NO_CACHING)) {
                return $next($request);
            }
        }

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
