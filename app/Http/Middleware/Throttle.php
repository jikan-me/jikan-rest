<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Throttle
{
    public $maxAttemptsPerDecayMinutes = 30;
    public $maxAttemptsPerConcurrency = 2;
    public $decayMinutes = 1;

    private $userRequests = [];

    public function handle(Request $request, Closure $next)
    {
        // don't throttle meta requests
//        if (\in_array('meta', $request->segments())) {
//            return $next($request);
//        }

        if (env('THROTTLE') === false) {
            return $next($request);
        }


        if (!is_null(env('THROTTLE_DECAY_MINUTES'))) {
            $this->decayMinutes = (int) env('THROTTLE_DECAY_MINUTES');
        }

        if (!is_null(env('THROTTLE_MAX_PER_DECAY_MINUTES'))) {
            $this->maxAttemptsPerDecayMinutes = (int) env('THROTTLE_MAX_PER_DECAY_MINUTES');
        }

        if (!is_null(env('THROTTLE_MAX_PER_CONCURRENCY'))) {
            $this->maxAttemptsPerConcurrency = (int) env('THROTTLE_MAX_PER_CONCURRENCY');
        }

        $signature = $this->resolveRequestSignature($request);
        $key = "user:{$signature}:" . time();

        $this->hit($key);

        $data = app('redis')->keys("user:{$signature}:*");
        foreach ($data as $user) {
            $this->userRequests[$user] = (int) app('redis')->get($user);
        }

        // throttle concurrent requests
        if (array_sum($this->userRequests) > $this->maxAttemptsPerDecayMinutes) {
            return response()->json([
                'error' => 'You are being rate limited [MAX: '.$this->maxAttemptsPerDecayMinutes.' requests/'.$this->decayMinutes.' minute(s)]'
            ], 429);
        }

        // requests per DECAY_MINUTES
        $requestsThisSecond = (int) app('redis')->get($key);
        if ($requestsThisSecond > $this->maxAttemptsPerConcurrency) {
            return response()->json([
                'error' => 'You are being rate limited [MAX: '.$this->maxAttemptsPerConcurrency.' requests/second]'
            ], 429);
        }

        return $next($request);
    }

    protected function resolveRequestSignature(Request $request) {
        if (env('SLAVE_INSTANCE') === true) {
            $ip = $request->header(env('SLAVE_CLIENT_IP_HEADER'));
            return sha1(
                'localhost' . '|' . $ip
            );
        }

        return sha1(
            $request->getHost() . '|' . $request->ip()
        );
    }

    protected function hit(string $key) {
        if (!app('redis')->exists($key)) {
            app('redis')->set($key, 0);
            app('redis')->expire($key, $this->decayMinutes*60);
        }
        app('redis')->incr($key);
    }
}
