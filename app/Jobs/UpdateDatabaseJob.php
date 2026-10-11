<?php

namespace App\Jobs;

use App\Http\HttpHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use MongoDB\BSON\UTCDateTime;


/**
 * Class UpdateDatabaseJob
 * @package App\Jobs
 */
class UpdateDatabaseJob extends Job
{


    public $timeout = 60;

    public $retryAfter = 60;

    protected $request;
    protected $requestUri;
    protected $requestType;
    protected $requestCacheTtl;
    protected $fingerprint;
    protected $table;

    /**
     * Create a new job instance.
     *
     * @param Request $request
     * @param $table
     */
    public function __construct(Request $request, $table)
    {
        $this->table = $table;
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);

        $this->requestType = HttpHelper::requestType($request);
        $this->requestCacheTtl = HttpHelper::requestCacheExpiry($this->requestType);
    }

    public function handle() : void
    {

        $response = app('GuzzleClient')
            ->request(
                'GET',
                config('app.url') . $this->requestUri,
                [
                    'headers' => [
                        'auth' => config('jikan.cache_bypass_token') // skips micro-caching middleware
                    ]
                ]
            );

        $cache = json_decode($response->getBody()->getContents(), true);
        unset($cache['request_hash'], $cache['request_cached'], $cache['request_cache_expiry'], $cache['DEVELOPMENT_NOTICE'], $cache['MIGRATION']);
        $cache = json_encode($cache);

        DB::table($this->table)
            ->where('request_hash', $this->fingerprint)
            ->update(array_merge(
            [
                'expiresAt' => new UTCDateTime((time()+$this->requestCacheTtl)*1000),
                'request_hash' => $this->fingerprint
            ],
            json_decode($cache, true)
        ));

        sleep(config('jikan.queue_delay_per_job'));
    }

    public function failed(\Exception $e)
    {
        Log::error($e->getMessage());
    }
}
