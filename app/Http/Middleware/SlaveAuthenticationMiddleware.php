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


        $slaveKey = $request->header('x-slave-key') ?? null;
        $clientIp = $request->header('x-client-ip') ?? null;

        if (is_null($slaveKey)) {
            return response()->json([
                'error' => 'Header x-slave-key is not set'
            ]);
        }

        if (is_null($clientIp)) {
            return response()->json([
                'error' => 'Forwarded Header x-client-ip is not set'
            ]);
        }

        if ($slaveKey !== env('SLAVE_KEY')) {
            return response()->json([
                'error' => 'Failed to verify slave key'
            ]);
        }

        return $next($request);
    }
}
