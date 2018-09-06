<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedisCache
{
    protected const CACHE_EXPIRY = 43200; // 6 hours

    public function handle(Request $request, Closure $next)
    {
        //debug
//        error_log(
//            $request->header('SLAVE_KEY_HEADER') . " | " . $request->header(env('SLAVE_CLIENT_IP_HEADER'))
//        );
        // pass on meta requests
        if (\in_array('meta', $request->segments())) {
            return $next($request);
        }

        $key = $request->getRequestUri();
        if (empty($request->segments())) {return $next($request);}
        if (!isset($request->segments()[1])){return $next($request);}

        $requestType = $request->segments()[1];
        if (!\in_array($request->segments()[0], ['v1', 'v2', 'v3'])) {
            $requestType = $request->segments()[0];
        }

        $hashKey = "request:{$requestType}:" . sha1($key);
        $cached = true;

        if (!app('redis')->exists($hashKey)) {

            $response = $next($request);
            $cached = false;

            if ($this->isError($response)) {
                return $response;
            }

            app('redis')->set(
                $hashKey,
                $response->original
            );
            app('redis')->expire($hashKey, self::CACHE_EXPIRY);
        }

        return response()->json(
            array_merge(
                [
                    'request_hash' => $hashKey,
                    'request_cached' => $cached,
                    'request_cache_expiry' => app('redis')->ttl($hashKey),
                ],
                json_decode(
                    app('redis')->get($hashKey),
                    true
                )
            )
        );
    }

    private function isError($response) {
        return isset($response->original['error']);
    }
}
