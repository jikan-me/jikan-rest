<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Http\Request;

class JikanResponse
{
    private $fingerprint;
    private $requestUri;
    private $requestType;
    private $requestCached = false;
    private $requestCacheExpiry;

    public function handle(Request $request, Closure $next)
    {
        if (empty($request->segments())) {return $next($request);}
        if (!isset($request->segments()[1])){return $next($request);}
        if (\in_array('meta', $request->segments())) {return $next($request);}

        $this->requestUri = $request->getRequestUri();
        $this->requestType = HttpHelper::requestType($request);
        $this->requestCacheExpiry = HttpHelper::requestCacheExpiry($this->requestType);
        $this->fingerprint = "request:{$this->requestType}:" . sha1($this->requestUri);
        $this->requestCached = (bool) app('redis')->exists($this->fingerprint);

        // Cache data from parser
        if (!$this->requestCached) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            app('redis')->set($this->fingerprint, $response->original);
            app('redis')->expire($this->fingerprint, $this->requestCacheExpiry);
        }

        // ETag
        if (
            $request->hasHeader('If-None-Match')
            && app('redis')->exists($this->fingerprint)
            && md5(app('redis')->get($this->fingerprint)) === $request->header('If-None-Match')
        ) {
            return response('', 304);
        }

        // Return cache
        $meta = [
            'request_hash' => $this->fingerprint,
            'request_cached' => $this->requestCached,
            'request_cache_expiry' => app('redis')->ttl($this->fingerprint)
        ];
        $cache = app('redis')->get($this->fingerprint);
        $cacheMutable = json_decode(app('redis')->get($this->fingerprint), true);
        $cacheMutable = $this->serializeEmptyObjects($this->requestType, $cacheMutable);

        return response()->json(
            array_merge($meta, $cacheMutable)
        )->setEtag(md5($cache));
    }

    private function serializeEmptyObjects($requestType, array $data)
    {
        if (!($requestType === 'anime' || $requestType === 'manga')) {
            return $data;
        }

        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass();
        }

        if (isset($data['related'])) {
            $related = $data['related'];
            $data['related'] = [];

            foreach ($related as $relation => $items) {
                $data['related'][] = [
                    'relation' => $relation,
                    'items' => $items
                ];
            }
        }

        return $data;
    }

}
