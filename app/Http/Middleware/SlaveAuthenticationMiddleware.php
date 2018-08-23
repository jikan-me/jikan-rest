<?php

namespace App\Http\Middleware;

use Closure;

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


        $slaveKey = $_GET['slave_key'] ?? null;

        if (is_null($slaveKey)) {
            return response()->json([
               'error' => 'Slave not configured properly'
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
