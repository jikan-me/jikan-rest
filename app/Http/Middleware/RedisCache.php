<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedisCache
{
    protected const CACHE_EXPIRY = 43200; // 6 hours

    private $fingerprint;
    private $cached = false;
    private $response;

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

        $this->fingerprint = "request:{$requestType}:" . sha1($key);
        $this->cached = (bool) app('redis')->exists($this->fingerprint);

        // ETag
        if (
            $request->hasHeader('If-None-Match')
            && app('redis')->exists($this->fingerprint)
            && md5(app('redis')->get($this->fingerprint)) === $request->header('If-None-Match')
        ) {
            return response('', 304);
        }


        if (!app('redis')->exists($this->fingerprint)) {

            $response = $next($request);

            if ($this->isError($response)) {
                return $response;
            }

            app('redis')->set(
                $this->fingerprint,
                $response->original
            );
            app('redis')->expire($this->fingerprint, self::CACHE_EXPIRY);
        }

        $data = app('redis')->get($this->fingerprint);
        $ttl = app('redis')->ttl($this->fingerprint);

        return response()->json(
            [
                'request_hash' => $this->fingerprint,
                'request_cached' => $this->cached,
                'request_cache_expiry' => $ttl,
            ]
            +
            json_decode($data, true)
        )
            ->setEtag(md5($data));
    }

    private function isError($response) : bool {
        return isset($response->original['error']);
    }
}
