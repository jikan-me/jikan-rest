<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedisCache
{
    private $fingerprint;
    private $cached = false;
    private $cacheExpiry = 43200; // 6 hours

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

        if ($requestType === "user") {
            $this->cacheExpiry = 300;
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
            app('redis')->expire($this->fingerprint, $this->cacheExpiry);
        }

        $data = app('redis')->get($this->fingerprint);
        $meta = [
            'request_hash' => $this->fingerprint,
            'request_cached' => $this->cached,
            'request_cache_expiry' => app('redis')->ttl($this->fingerprint)
        ];

        $return = $meta + json_decode($data, true);

        if ($requestType === 'anime' || $requestType === 'manga') {
            if (isset($return['related'])) {
                if (\count($return['related']) === 0) {
                    $return['related'] = new \stdClass();
                }
            }
        }

        return response()->json(
            $return
        )
            ->setEtag(md5($data));
    }

    private function isError($response) : bool {
        return isset($response->original['error']);
    }
}
