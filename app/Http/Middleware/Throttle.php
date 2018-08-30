<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Throttle
{
    public const MAX_ATTEMPTS_PER_DECAY_MINUTES = 30;
    public const MAX_ATTEMPTS_PER_CONCURRENCY = 2;
    public const DECAY_MINUTES = 1;

    private $userRequests = [];

    public function handle(Request $request, Closure $next)
    {
        // don't throttle meta requests
        if (\in_array('meta', $request->segments())) {
            return $next($request);
        }

        $signature = $this->resolveRequestSignature($request);
        $key = "user:{$signature}:" . time();

        $this->hit($key);

        $data = app('redis')->keys("user:{$signature}:*");
        foreach ($data as $user) {
            $this->userRequests[$user] = (int) app('redis')->get($user);
        }

        // throttle concurrent requests
        if (array_sum($this->userRequests) > self::MAX_ATTEMPTS_PER_DECAY_MINUTES) {
            return response()->json([
                'error' => 'You are being rate limited [MAX: '.self::MAX_ATTEMPTS_PER_DECAY_MINUTES.' requests/'.self::DECAY_MINUTES.' minute(s)]'
            ], 429);
        }

        // requests per DECAY_MINUTES
        $requestsThisSecond = (int) app('redis')->get($key);
        if ($requestsThisSecond > self::MAX_ATTEMPTS_PER_CONCURRENCY) {
            return response()->json([
                'error' => 'You are being rate limited [MAX: '.self::MAX_ATTEMPTS_PER_CONCURRENCY.' requests/second]'
            ], 429);
        }

        return $next($request);
    }

    protected function resolveRequestSignature(Request $request) {
        if (env('SLAVE_INSTANCE') === true) {
            $ip = $request->header('x-client-ip');
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
            app('redis')->expire($key, self::DECAY_MINUTES*60);
        }
        app('redis')->incr($key);
    }
}
