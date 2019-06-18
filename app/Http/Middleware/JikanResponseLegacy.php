<?php

/**
 * This middleware was used up to REST v3.2; it was previously named `JikanResponse`
 *
 * It works by storing cache with TTL
 * Redis automatically removes any cache that's past it's TTL
 *
 * This middleware has been succeeded by JikanResponseHandler
 */

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use Closure;
use Illuminate\Http\Request;

class JikanResponseLegacy
{
    private $fingerprint;
    private $requestUri;
    private $requestType;
    private $requestCached = false;
    private $requestCacheExpiry;

    public function handle(Request $request, Closure $next)
    {
        if (empty($request->segments())) {
            return $next($request);
        }
        if (!isset($request->segments()[1])) {
            return $next($request);
        }
        if (\in_array('meta', $request->segments())) {
            return $next($request);
        }

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
        $meta = $this->generateMeta($request);

        $cache = app('redis')->get($this->fingerprint);
        $cacheMutable = json_decode(app('redis')->get($this->fingerprint), true);

        $cacheMutable = $this->cacheMutation($cacheMutable);

        return response()
            ->json(
                array_merge($meta, $cacheMutable)
            )
            ->setEtag(
                md5($cache)
            )
            ->withHeaders([
                'X-Request-Hash' => $this->fingerprint,
                'X-Request-Cached' => $this->requestCached,
                'X-Request-Cache-Expiry' => app('redis')->ttl($this->fingerprint)
            ]);
    }

    private function generateMeta(Request $request) : array
    {
        $version = HttpHelper::requestAPIVersion($request);

        $meta = [
            'request_hash' => $this->fingerprint,
            'request_cached' => $this->requestCached,
            'request_cache_expiry' => app('redis')->ttl($this->fingerprint)
        ];

        switch ($version) {
            case 2:
                $meta = array_merge([
                    'DEPRECIATION_NOTICE' => 'THIS VERSION WILL BE DEPRECIATED ON JULY 01st, 2019.',
                ], $meta);
                break;
            case 4:
                // remove cache data from JSON response and send as headers
                unset($meta['request_cached'], $meta['request_cache_expiry']);
                $meta = array_merge([
                    'DEVELOPMENT_NOTICE' => 'THIS VERSION IS IN TESTING. DO NOT USE FOR PRODUCTION.',
                    'MIGRATION' => 'https://github.com/jikan-me/jikan-rest/blob/master/MIGRATION.MD',
                ], $meta);
                break;
        }


        return $meta;
    }

    private function cacheMutation(array $data) : string
    {
        if (!($this->requestType === 'anime' || $this->requestType === 'manga')) {
            return $data;
        }

        // Fix JSON response for empty related object
        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass();
        }
    }
}
