<?php

/**
 * This middleware is the successor of JikanResponseLegacy; used for REST v3.3+
 *
 * It works by storing cache with no automated TTL handling by Redis
 *
 * If a request is past it's TTL, it queues an update instead of removing the cache followed by fetching a new one
 * Update queues are automated.
 *
 * Therefore,
 * - if MyAnimeList is down or rate-limits the response, stale cache is served
 * - if cache expires, the client doesn't have to wait longer for the server to fetch+parse the new response
 */


namespace App\Http\Middleware;

use App\Http\HttpHelper;
use App\Jobs\UpdateCacheJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JikanResponseHandler
{
    private $requestUri;
    private $requestUriHash;
    private $requestType;
    private $requestCacheExpiry;
    private $requestCached = false;
    private $requestCacheTtl;

    private $fingerprint;
    private $cacheExpiryFingerprint;

    private $controllerName;
    private $controllerMethod;

    public function handle(Request $request, Closure $next)
    {

        if (empty($request->segments())) {return $next($request);}
        if (!isset($request->segments()[1])){return $next($request);}
        if (\in_array('meta', $request->segments())) {return $next($request);}
        if ($request->header('auth') === env('APP_KEY')) {return $next($request);}


        $this->requestUri = $request->getRequestUri();
        $this->requestUriHash = sha1(env('APP_URL') . $this->requestUri);
        $this->requestType = HttpHelper::requestType($request);

        $this->requestCacheTtl = HttpHelper::requestCacheExpiry($this->requestType);
        $this->requestCacheExpiry = time() + $this->requestCacheTtl;

        $this->fingerprint = "request:{$this->requestType}:{$this->requestUriHash}";
        $this->cacheExpiryFingerprint = "ttl:{$this->fingerprint}";

        $this->requestCached = (bool) app('redis')->exists($this->fingerprint);

        // Cache if it doesn't exist
        if (!$this->requestCached) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            app('redis')->set($this->fingerprint, $response->original);
            app('redis')->set($this->cacheExpiryFingerprint, $this->requestCacheExpiry);
        }

        // If cache is expired, queue for an update
        $this->requestCacheExpiry = (int) app('redis')->get($this->cacheExpiryFingerprint);


        if ($this->requestCacheExpiry < time()) {

            $queueFingerprint = "queue_update:{$this->fingerprint}";

            // Don't duplicate the queue for same request
            if (!app('redis')->exists($queueFingerprint)) {
                app('redis')->set($queueFingerprint, 1);
                dispatch(
                    (new UpdateCacheJob($request))->delay(
                        env('QUEUE_DELAY_PER_JOB', 5)
                    )
                );

            } else {
                Log::info("Duplicate ({$queueFingerprint})");
            }
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
                'X-Request-Cache-Expiry' => app('redis')->get($this->cacheExpiryFingerprint) - time()
            ]);

    }

    private function generateMeta(Request $request) : array
    {
        $version = HttpHelper::requestAPIVersion($request);

        $meta = [
            'request_hash' => $this->fingerprint,
            'request_cached' => $this->requestCached,
            'request_cache_expiry' => app('redis')->get($this->cacheExpiryFingerprint) - time()
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
}
