<?php

namespace App\Http\Middleware;

use App\Http\HttpHelper;
use App\Jobs\UpdateCacheJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheResolver
{
    private $requestUri;
    private $requestUriHash;
    private $requestType;
    private $requestCacheExpiry = 0;
    private $requestCached = false;
    private $requestCacheTtl;

    private $fingerprint;
    private $cacheExpiryFingerprint;

    private $route;

    private $queueable = true;

    private const NON_QUEUEABLE = [
        'UserController@profile',
        'UserController@history',
        'UserController@friends',
        'UserController@animelist',
        'UserController@mangalist',
    ];

    private const HIGH_PRIORITY_QUEUE = [
        'ScheduleController@main'
    ];

    public function handle(Request $request, Closure $next)
    {

        if (!env('CACHING')) {
            return $next($request);
        }

        if ($request->header('auth') === env('APP_KEY')) {
            return $next($request);
        }

        if (empty($request->segments())) {
            return $next($request);
        }

        if (!isset($request->segments()[1])) {
            return $next($request);
        }

        if (\in_array('meta', $request->segments())) {
            return $next($request);
        }

        $this->requestUriHash = HttpHelper::getRequestUriHash($request);
        $this->requestType = HttpHelper::requestType($request);
        $this->requestCacheTtl = HttpHelper::requestCacheExpiry($this->requestType);
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);
        $this->cacheExpiryFingerprint = "ttl:{$this->fingerprint}";
        $this->requestCached = Cache::has($this->fingerprint);

        $this->route = explode('\\', $request->route()[1]['uses']);
        $this->route = end($this->route);

        // Check if request is in the 404 cache pool @todo: separate as middleware
        if (Cache::has("request:404:{$this->requestUriHash}")) {
            return response()
                ->json([
                    'status' => 404,
                    'type' => 'BadResponseException',
                    'message' => 'Resource does not exist',
                    'error' => Cache::get("request:404:{$this->requestUriHash}")
                ], 404);
        }

        // Is the request queueable?
        if (\in_array($this->route, self::NON_QUEUEABLE) || env('CACHE_METHOD', 'legacy') === 'legacy') {
            $this->queueable = false;
        }

        // Cache if it doesn't exist
        if (!$this->requestCached) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            Cache::forever($this->fingerprint, $response->original);
            Cache::forever($this->cacheExpiryFingerprint, time() + $this->requestCacheTtl);
        }

        // If cache is expired, handle it depending on whether it's queueable
        $this->requestCacheExpiry = (int) Cache::get($this->cacheExpiryFingerprint);

        if ($this->requestCached && $this->requestCacheExpiry <= time() && !$this->queueable) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            Cache::forever($this->fingerprint, $response->original);
            Cache::forever($this->cacheExpiryFingerprint, time() + $this->requestCacheTtl);
            $this->requestCacheExpiry = (int) Cache::get($this->cacheExpiryFingerprint);
        }

        if ($this->queueable && $this->requestCacheExpiry <= time()) {
            $queueFingerprint = "queue_update:{$this->fingerprint}";
            $queueHighPriority = \in_array($this->route, self::HIGH_PRIORITY_QUEUE);

            // Don't duplicate the job in the queue for same request
            if (!app('redis')->exists($queueFingerprint)) {
                app('redis')->set($queueFingerprint, 1);

                dispatch(
                    (new UpdateCacheJob($request))
                        ->onQueue($queueHighPriority ? 'high' : 'low')
                );
            }
        }

        // Return response
        $meta = $this->generateMeta($request);

        $cache = Cache::get($this->fingerprint);
        $cacheMutable = json_decode($cache, true);
        $cacheMutable = $this->cacheMutation($cacheMutable);

        $response = array_merge($meta, $cacheMutable);

        // Allow microcaching if it's enabled and the cache driver is set to file
        if (env('MICROCACHING', true) && env('CACHE_DRIVER', 'file') === 'file') {
            MicroCaching::setMicroCache($this->fingerprint, $response);
        }

        // Build and return response
        return response()
            ->json(
                $response
            )
            ->setEtag(
                md5($cache)
            )
            ->withHeaders([
                'X-Request-Hash' => $this->fingerprint,
                'X-Request-Cached' => $this->requestCached,
                'X-Request-Cache-Ttl' => (int) $this->requestCacheExpiry - time()
            ])
            ->setExpires((new \DateTime())->setTimestamp($this->requestCacheExpiry));
    }

    private function generateMeta(Request $request) : array
    {
        $version = HttpHelper::requestAPIVersion($request);

        $meta = [
            'request_hash' => $this->fingerprint,
            'request_cached' => $this->requestCached,
            'request_cache_expiry' => (int) $this->requestCacheExpiry - time()
        ];

        switch ($version) {
            case 2:
                $meta = array_merge([
                    'DEPRECIATION_NOTICE' => 'THIS VERSION WILL BE DEPRECIATED ON JULY 01st, 2019.',
                ], $meta);
                break;
            case 4:
                // remove cache data from JSON response as it's sent as headers
                unset($meta['request_cached'], $meta['request_cache_expiry']);
                $meta = array_merge([
                    'DEVELOPMENT_NOTICE' => 'THIS VERSION IS IN TESTING. DO NOT USE FOR PRODUCTION.',
                    'MIGRATION' => 'https://github.com/jikan-me/jikan-rest/blob/master/MIGRATION.MD',
                ], $meta);
                break;
        }

        return $meta;
    }

    private function cacheMutation(array $data) : array
    {
        if (!($this->requestType === 'anime' || $this->requestType === 'manga')) {
            return $data;
        }

        // Fix JSON response for empty related object
        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass();
        }

        return $data;
    }
}
