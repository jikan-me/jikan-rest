<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Support\Facades\Cache;

class MicroCaching
{
    private const NO_CACHING = [
        'RandomController@anime',
        'RandomController@manga',
        'RandomController@characters',
        'RandomController@people',
        'RandomController@users',
    ];

    private readonly bool $isEnabled;

    public function __construct(JikanConfig $jikanConfig)
    {
        $this->isEnabled = $jikanConfig->isMicroCachingEnabled();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->isEnabled) {
            return $next($request);
        }
        // Laravel 13: Route is no longer array-accessible (Lumen used to return one).
        // Read the controller action (e.g. "App\Http\Controllers\V4DB\RandomController@anime")
        // and compare the last segment so the NO_CACHING bypass applies again.
        if ($request->route()) {
            $action = $request->route()->getActionName();
            $route = explode('\\', $action);
            $route = end($route);

            if (\in_array($route, self::NO_CACHING)) {
                return $next($request);
            }
        }

        // Allow bypass of caching if the dedicated cache-bypass token is
        // supplied in the auth header (was previously APP_KEY). Empty token
        // => no bypass.
        $bypassToken = (string) config('jikan.cache_bypass_token');
        if ($bypassToken !== '' && $request->header('auth') === $bypassToken) {
            return $next($request);
        }

        // cache /v*/* paths only
        if (
            empty($request->segments())
            || !isset($request->segments()[1])
        ) {
            return $next($request);
        }

        $fingerprint = sha1(HttpHelper::resolveRequestFingerprint($request));

        // if cache exists, return cache
        if (Cache::has($fingerprint)) {
            $response = \json_decode(Cache::get($fingerprint), true);

            unset($response['meta']);
            unset($response['links']);

            return response()
                ->json($response);
        }

        // set cache
        $response = $next($request);

        try {
            Cache::add(
                $fingerprint,
                json_encode(
                    $response->getData()
                ),
                config('jikan.microcaching_expire')
            );

        } catch (\Exception $e) {
            // ->getData() is a BadMethodCallException if HTTP status is not 200
            // so ignore it
        }

        return $response;
    }

}
