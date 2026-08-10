<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Configurable rate limiting middleware for the Jikan REST API.
 *
 * Enforces per-IP rate limits to prevent abuse and upstream MAL overload.
 * Limits are configurable via .env: RATE_LIMIT_PER_SECOND, RATE_LIMIT_PER_MINUTE.
 *
 * Returns proper 429 responses with Retry-After and X-RateLimit-* headers
 * so clients can implement respectful backoff.
 */
class RateLimitMiddleware
{
    private readonly bool $enabled;
    private readonly int $perSecond;
    private readonly int $perMinute;

    public function __construct()
    {
        $this->enabled = (bool) env('RATE_LIMIT_ENABLED', true);
        $this->perSecond = (int) env('RATE_LIMIT_PER_SECOND', 3);
        $this->perMinute = (int) env('RATE_LIMIT_PER_MINUTE', 60);
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->enabled) {
            return $next($request);
        }

        // Allow bypass if dedicated secret key is supplied in header (for internal/admin use)
        $bypassKey = env('RATE_LIMIT_BYPASS_KEY');
        if (!empty($bypassKey) && $request->header('X-RateLimit-Bypass-Key') === $bypassKey) {
            return $next($request);
        }

        $clientIp = $this->resolveClientIp($request);

        // Check per-second limit
        $secondResult = $this->checkLimit($clientIp, 'sec', $this->perSecond, 1);
        if ($secondResult !== null) {
            return $secondResult;
        }

        // Check per-minute limit
        $minuteResult = $this->checkLimit($clientIp, 'min', $this->perMinute, 60);
        if ($minuteResult !== null) {
            return $minuteResult;
        }

        // Request is allowed — add rate limit info headers to the response
        $response = $next($request);

        $minuteRemaining = $this->getRemaining($clientIp, 'min', $this->perMinute);

        try {
            $response->headers->set('X-RateLimit-Limit', (string) $this->perMinute);
            $response->headers->set('X-RateLimit-Remaining', (string) max(0, $minuteRemaining));
        } catch (\Exception $e) {
            // Some response types may not support headers — fail silently
        }

        return $response;
    }

    /**
     * Check a rate limit bucket. Returns a 429 response if exceeded, null if OK.
     */
    private function checkLimit(string $clientIp, string $bucket, int $maxHits, int $decaySeconds): ?\Illuminate\Http\JsonResponse
    {
        $key = "ratelimit:{$clientIp}:{$bucket}";

        try {
            $hits = (int) Cache::get($key, 0);

            if ($hits >= $maxHits) {
                $ttl = $this->getTtl($key, $decaySeconds);
                $retryAfter = max(1, $ttl);

                return response()->json([
                    'status' => 429,
                    'type' => 'TooManyRequestsException',
                    'message' => 'You are being rate limited. Please retry after the Retry-After period.',
                    'error' => 'Rate limit exceeded',
                    'report_url' => null,
                ], 429)->withHeaders([
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => $maxHits,
                    'X-RateLimit-Remaining' => 0,
                ]);
            }

            // Increment the counter
            if ($hits === 0) {
                Cache::put($key, 1, $decaySeconds);
            } else {
                Cache::increment($key);
            }
        } catch (\Exception $e) {
            // If cache is unavailable, allow the request through rather than blocking
            return null;
        }

        return null;
    }

    /**
     * Get remaining hits for a bucket.
     */
    private function getRemaining(string $clientIp, string $bucket, int $maxHits): int
    {
        $key = "ratelimit:{$clientIp}:{$bucket}";

        try {
            $hits = (int) Cache::get($key, 0);
            return $maxHits - $hits;
        } catch (\Exception $e) {
            return $maxHits;
        }
    }

    /**
     * Get the TTL of a cache key in seconds, falling back to default decay time.
     */
    private function getTtl(string $key, int $defaultDecay): int
    {
        try {
            // Redis supports TTL natively
            $store = Cache::getStore();
            if (method_exists($store, 'connection')) {
                $ttl = (int) $store->connection()->ttl($key);
                if ($ttl > 0) {
                    return $ttl;
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return $defaultDecay;
    }

    /**
     * Resolve the client IP, respecting X-Forwarded-For only if trusted proxies are configured.
     */
    private function resolveClientIp(Request $request): string
    {
        $trustedProxies = env('TRUSTED_PROXIES');

        // Only trust X-Forwarded-For if trusted proxies are explicitly configured in env
        if (!empty($trustedProxies)) {
            $forwardedFor = $request->header('X-Forwarded-For');
            if ($forwardedFor) {
                // X-Forwarded-For can be comma-separated; the first IP is the real client
                $ips = array_map('trim', explode(',', $forwardedFor));
                return $ips[0];
            }

            $realIp = $request->header('X-Real-Ip');
            if ($realIp) {
                return $realIp;
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }
}
}
