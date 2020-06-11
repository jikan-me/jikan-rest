<?php

namespace App\Http\Middleware;

use App\DatabaseHandler;
use App\Events\SourceHealthEvent;
use App\Http\HttpHelper;
use App\Jobs\UpdateCacheJob;
use App\Jobs\UpdateDatabaseJob;
use App\Providers\SourceHealthServiceProvider;
use Closure;
use Flipbox\LumenGenerator\LumenGeneratorServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\MongodbServiceProvider;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Collection;
use r\Queries\Control\Http;

class DatabaseResolver
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

    private $table;

    public const SKIP = [
        'SearchController@anime',
        'SearchController@manga',
        'TopController@anime',
        'TopController@manga',
        'GenreController@anime',
        'GenreController@manga',
        'ProducerController@main',
        'MagazineController@main',
        'ScheduleController@main'
    ];

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

        if (HttpHelper::requestAPIVersion($request) >= 4) {
            return $next($request);
        }

        $this->requestUriHash = HttpHelper::getRequestUriHash($request);
        $this->requestType = HttpHelper::requestType($request);
        $this->requestCacheTtl = HttpHelper::requestCacheExpiry($this->requestType);
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);
        $this->cacheExpiryFingerprint = "ttl:{$this->fingerprint}";

        $this->route = explode('\\', $request->route()[1]['uses']);
        $this->route = end($this->route);

        if (\in_array($this->route, self::SKIP)) {
            return $next($request);
        }

        $db = new DatabaseHandler();
        $this->table = $db::getMappedTableName($this->route);
        $this->requestCached = DB::table($this->table)->where('request_hash', $this->fingerprint)->exists();

        // Is the request queueable?
        if (\in_array($this->route, self::NON_QUEUEABLE) || env('DB_METHOD', 'legacy') === 'legacy') {
            $this->queueable = false;
        }

        // If cache does not exist
        if (!$this->requestCached) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            $this->insertCache($response);
        }

        // Fetch Cache & Generate Meta
        $meta = $this->generateMeta($request);

        $cache = DB::table($this->table)->where('request_hash', $this->fingerprint)->get();
        $cacheMutable = json_decode($cache, true)[0];
        $cacheMutable = $this->cacheMutation($cacheMutable);

        // If cache is expired, handle it depending on whether it's queueable
        $expiresAt = $cacheMutable['expiresAt']['$date']['$numberLong']/1000;

        if ($this->requestCached && $expiresAt < time() && !$this->queueable) {
            $response = $next($request);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

            $this->insertCache($response);
        }



        if ( $this->queueable && $expiresAt < time()) {
            $queueHighPriority = \in_array($this->route, self::HIGH_PRIORITY_QUEUE);

            // Don't duplicate the job in the queue for same request
            $job = DB::table(env('QUEUE_TABLE', 'jobs'))->where('request_hash', $this->fingerprint);

            if (!$job->exists()) {
                dispatch(
                    (new UpdateDatabaseJob($request, $this->table))
                        ->onQueue($queueHighPriority ? 'high' : 'low')
                );
            }
        }

        $response = array_merge($meta, $cacheMutable);
        unset($response['createdAt'], $response['expireAfterSeconds'], $response['_id'], $response['expiresAt']);

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

    public function insertCache($response)
    {
        DB::table($this->table)->insert(array_merge(
            [
                'expiresAt' => new UTCDateTime((time()+$this->requestCacheTtl)*1000),
                'request_hash' => $this->fingerprint
            ],
            json_decode($response->original, true)
        ));
    }
}
