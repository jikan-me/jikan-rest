<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class BrownoutMiddleware
 * @package App\Http\Middleware
 *
 * Brownout will occur for the first 15 minutes of every other hour
 */
class BrownoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (env('APP_BROWNOUT') == false) {
            return $next($request);
        }

        $time = date("Hi");
        $hour = (int) substr($time, 0, 2);
        $minutes = (int) substr($time, 2, 3);
        $isEvenHour = $hour % 2 == 0;

        if ($isEvenHour && $minutes <= 15) {
            return response('', 410);
        }

        return $next($request);
    }
}
