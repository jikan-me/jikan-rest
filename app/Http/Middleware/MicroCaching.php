<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Support\Env;
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
        if (isset($request->route()[1]['uses'])) {
            $route = explode('\\', $request->route()[1]['uses']);
            $route = end($route);
            if (\in_array($route, self::NO_CACHING)) {
                return $next($request);
            }
        }

        // Allow bypass of caching if APP_KEY supplied in auth header
        if ($request->header('auth') === env('APP_KEY')) {
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
            return response()
                ->json(
                    \json_decode(Cache::get($fingerprint), true)
                );
        }

        // set cache
        $response = $next($request);

        try {
            Cache::add(
                $fingerprint,
                json_encode(
                    $response->getData()
                ),
                env('MICROCACHING_EXPIRE', 60)
            );

        } catch (\Exception $e) {
            // ->getData() is a BadMethodCallException if HTTP status is not 200
            // so ignore it
        }

        return $response;
    }

}
