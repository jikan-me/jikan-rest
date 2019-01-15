<?php

namespace App\Http\Middleware;

use Closure;

class Meta
{
    private $request;

    public function handle($request, Closure $next)
    {
        // pass on meta requests
//        if (\in_array('meta', $request->segments())) {
//            return $next($request);
//        }

        $requestUri = $request->getRequestUri();
        $requestUri = str_replace(['/v1', '/v2', '/v3'], '', $requestUri);

        $response = $next($request);
        if (isset($response->original['error'])) {
            return $response;
        }

        $this->updateMeta("requests:today", $requestUri, 86400);
        $this->updateMeta("requests:weekly", $requestUri, 604800);
        $this->updateMeta("requests:monthly", $requestUri, 2629746);


        return $response;
    }

    private function updateMeta($key, $req, $expire) {
        $hashKey = $key . ":" . $req;
        if (!app('redis')->exists($hashKey)) {
            app('redis')->set($hashKey, 0);
            app('redis')->expire($hashKey, $expire);
        }

        app('redis')->incr($hashKey);
    }

}
