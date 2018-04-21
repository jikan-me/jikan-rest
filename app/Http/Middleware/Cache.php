<?php

namespace App\Http\Middleware;

use Closure;

class Cache
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
        $response = $next($request);
        $data = $response->original;

        if (!array_key_exists('error', $data)) {
        }

        return $response;
    }
}
