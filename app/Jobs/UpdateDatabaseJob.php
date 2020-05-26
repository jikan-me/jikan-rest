<?php

namespace App\Jobs;

use App\Http\HttpHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


/**
 * Class UpdateDatabaseJob
 * @package App\Jobs
 */
class UpdateDatabaseJob extends Job
{


    public $timeout = 60;

    public $retryAfter = 60;

    /**
     * @var string
     */
    protected $requestUri;
    protected $requestType;
    protected $requestCacheTtl;
    protected $fingerprint;
    protected $cacheExpiryFingerprint;
    protected $requestCached;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->fingerprint = HttpHelper::resolveRequestFingerprint($request);

        $this->requestCached = DB::table(env('QUEUE_TABLE', 'jobs'))->where('request_hash', $this->fingerprint);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle() : void
    {
        $client = new Client();

        $response = $client
            ->request(
                'GET',
                env('APP_URL') . $this->requestUri,
                [
                    'headers' => [
                        'auth' => env('APP_KEY') // skip middleware
                    ]
                ]
            );

        $cache = json_decode($response->getBody()->getContents(), true);
        unset($cache['request_hash'], $cache['request_cached'], $cache['request_cache_expiry']);
        $cache = json_encode($cache);

        sleep((int) env('QUEUE_DELAY_PER_JOB', 5));
    }

    public function failed(\Exception $e)
    {
        Log::error($e->getMessage());
    }
}
