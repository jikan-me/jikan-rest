<?php

namespace App\Jobs;

use App\Http\HttpHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class UpdateCacheJob
 * @package App\Jobs
 */
class UpdateCacheJob extends Job
{

    /**
     * @var string
     */
    protected $requestUri;
    protected $requestUriHash;
    protected $requestType;
    protected $requestCacheTtl;
    protected $requestCacheExpiry;
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
        $this->requestUri = $request->getRequestUri();

        $this->requestUriHash = sha1(env('APP_URL') . $this->requestUri);
        $this->requestType = HttpHelper::requestType($request);

        $this->requestCacheTtl = HttpHelper::requestCacheExpiry($this->requestType);
        $this->requestCacheExpiry = time() + $this->requestCacheTtl;

        $this->fingerprint = "request:{$this->requestType}:{$this->requestUriHash}";
        $this->cacheExpiryFingerprint = "ttl:{$this->fingerprint}";

        $this->requestCached = (bool) app('redis')->exists($this->fingerprint);
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle() : void
    {
        $queueFingerprint = "queue_update:{$this->fingerprint}";

        $client = new Client();

        $response = $client
            ->request(
                'GET',
                env('APP_URL') . $this->requestUri,
                [
                    'headers' => [
                        'auth' => env('APP_ADMIN_KEY') // skip middleware
                    ]
                ]
            );

        $cache = json_decode($response->getBody()->getContents(), true);
        unset($cache['request_hash'], $cache['request_cached'], $cache['request_cache_expiry']);
        $cache = json_encode($cache);


        app('redis')->set($this->fingerprint, $cache);
        app('redis')->set($this->cacheExpiryFingerprint, $this->requestCacheExpiry);
        app('redis')->del($queueFingerprint);
    }

    public function failed(\Exception $e)
    {
        Log::error($e->getMessage());
    }
}
