<?php

namespace App\Http\Middleware;

use Closure;
use App\Events\SourceHeartbeatEvent;

class SourceHeartbeatMonitor
{
    /**
     * Handle an incoming request.
     *
     * Monitors response status codes to track upstream MAL health.
     * Emits GOOD_HEALTH for successful responses and BAD_HEALTH for
     * server errors (5xx) that indicate MAL/scraper issues.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        try {
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 500) {
                // Server error — MAL or scraper is failing
                event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $statusCode));
            } elseif ($statusCode >= 200 && $statusCode < 400) {
                // Successful response
                event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, $statusCode));
            }
            // 4xx errors are client errors, not indicative of MAL health
        } catch (\Exception $e) {
            // Heartbeat tracking should never break the response
        }

        return $response;
    }
}

