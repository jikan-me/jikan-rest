<?php

namespace App\Http\Middleware;

use Closure;
use App\Events\SourceHeartbeatEvent;

class SourceHeartbeatMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, 200));

        return $next($request);
    }
}
