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


        $this->updateMeta("requests:today", $req, 86400);
        $this->updateMeta("requests:weekly", $req, 604800);
        $this->updateMeta("requests:monthly", $req, 2629746);


        return $response;
    }

    private function updateMeta($key, $req, $expire) {
        $hashKey = $key . ":" . $req;
        if (!app('redis')->exists($hashKey)) {
            app('redis')->set($hashKey, 0);
            app('redis')->expire($hashKey, $expire);
        }
        app('redis')->incrBy($hashKey, 1);
    }

}
