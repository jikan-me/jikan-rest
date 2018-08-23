<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class Cachet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            is_null(env('CACHET'))
            ||
            env('CACHET') === false
        ) {
            return $next($request);
        }

        // cachet env token check
        $cachetToken = env('CACHET_TOKEN') ?? null;
        $cachetApiUrl = env('CACHET_API_URL') ?? null;

        if (is_null($cachetToken)) {
            throw new \Exception("Cachet token not set");
        }

        if (is_null($cachetApiUrl)) {
            throw new \Exception("Cachet API URL not set");
        }

        $client = new Client([
            'base_uri' => $cachetApiUrl,
            'timeout' => 1.0,
            'headers' => [
                'X-Cachet-Token' => $cachetToken
            ]
        ]);

//        try {
            $response = $client->request('POST', 'metrics/1/points', [
                'form_params' => [
                    'value' => 1,
                    'timestamp' => time()
                ]
            ]);
//        } catch (ConnectException $e) {
//        }


        return $next($request);
    }
}
