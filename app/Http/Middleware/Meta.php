<?php

namespace App\Http\Middleware;

use Closure;

class Meta
{

    private $request;

    public function handle($request, Closure $next)
    {
        $req = $_SERVER['REQUEST_URI'];
        $req_type = explode('/', $req)[1];

        $response = $next($request);

        if (isset($response->original['error'])) {
            return $response;
        }

        $date = date("m-o");
        $time = round(microtime(true) * 1000);
        $key = "requests:".$date.":".$time;

        if (!app('redis')->exists($key)) {
            app('redis')->hMSet($key, [
                'key' => $key,
                'time' => $time,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'request' => $req,
                'request_type' => $req_type
                // 'count' => 0
            ]);

            app('redis')->sAdd('requests', $key);
        }

        // app('redis')->hIncrBy($key, 'count', 1);

        return $response;
    }
}
