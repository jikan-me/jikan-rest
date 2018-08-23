<?php

namespace App\Http\Middleware;

use Closure;

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

        // add metrics

        return $next($request);
    }
}
