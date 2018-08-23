<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SlaveAuthenticationMiddleware
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
            is_null(env('SLAVE_INSTANCE'))
            ||
            empty(env('SLAVE_INSTANCE'))
            ||
            env('SLAVE_INSTANCE') === false
        ) {
            return response()->json([
                'error' => 'Slave not configured properly'
            ]);
        }

        $slaveKeyHeader = env('SLAVE_KEY_HEADER');
        $slaveClientIpHeader = env('SLAVE_CLIENT_IP_HEADER');


        $slaveKey = $request->header($slaveKeyHeader) ?? null;
        $clientIp = $request->header($slaveClientIpHeader) ?? null;

        if (is_null($slaveKey)) {
            return response()->json([
                'error' => "Header \"{$slaveKeyHeader}\" is not set"
            ]);
        }

        if (is_null($clientIp)) {
            return response()->json([
                'error' => "Forwarded Header \"{$slaveClientIpHeader}\" is not set"
            ]);
        }

        if ($slaveKey !== env('SLAVE_KEY')) {
            return response()->json([
                'error' => "Slave verification failed; \"{$slaveKeyHeader}\" mismatch"
            ]);
        }

        return $next($request);
    }
}
