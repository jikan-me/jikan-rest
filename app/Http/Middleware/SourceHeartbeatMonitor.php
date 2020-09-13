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
        if (env('SOURCE_BAD_HEALTH_FAILOVER') && env('DB_CACHING')) {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, 200));
        }

        return $next($request);
    }
}
