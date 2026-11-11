<?php

namespace App\Http\Middleware;

use App\Events\SourceHeartbeatEvent;
use Closure;
use Illuminate\Http\Request;

class SourceHeartbeatMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, 200));

        return $next($request);
    }
}
