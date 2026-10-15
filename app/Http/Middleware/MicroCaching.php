<?php

namespace App\Http\Middleware;

use App\Support\HttpHelper;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MicroCaching
{
    private const NO_CACHING = [
        'v4.random.anime',
        'v4.random.manga',
        'v4.random.characters',
        'v4.random.people',
        'v4.random.users',
    ];

    private readonly bool $isEnabled;

    public function __construct(JikanConfig $jikanConfig)
    {
        $this->isEnabled = $jikanConfig->isMicroCachingEnabled();
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->isEnabled) {
            return $next($request);
        }
        // Route name of the matched route, e.g. "v4.random.anime" (see HttpHelper::getRouteName()).
        if (\in_array(HttpHelper::getRouteName($request), self::NO_CACHING, true)) {
            return $next($request);
        }

        // cache /v*/* paths only
        if (
            empty($request->segments())
            || ! isset($request->segments()[1])
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
