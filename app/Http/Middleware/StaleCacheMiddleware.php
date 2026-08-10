<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Stale Cache Middleware for Jikan REST API.
 *
 * Catches upstream errors (502, 503, 504) and serves stale cached responses
 * instead of propagating failures to the client.
 *
 * This implements a "stale-while-revalidate" pattern at the HTTP layer:
 * - On success: caches the response for future stale serving
 * - On upstream failure: serves the last known good response with X-Jikan-Stale header
 * - On no cache available: passes through the error as-is
 */
class StaleCacheMiddleware
{
    private const STALE_CACHE_PREFIX = 'stale_response:';

    /**
     * HTTP status codes that trigger stale cache serving.
     */
    private const RETRYABLE_STATUS_CODES = [502, 503, 504];

    public function __construct()
    {
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
        $fingerprint = $this->getFingerprint($request);

        try {
            $response = $next($request);

            $statusCode = $response->getStatusCode();

            // If response is successful, cache it for stale serving later
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->cacheResponse($fingerprint, $response);
                return $response;
            }

            // If response is a retryable error, try to serve stale cache
            if (in_array($statusCode, self::RETRYABLE_STATUS_CODES)) {
                $staleResponse = $this->getStaleResponse($fingerprint);
                if ($staleResponse !== null) {
                    return $staleResponse;
                }
            }

            return $response;

        } catch (\Exception $e) {
            // If an exception occurred during request processing, try stale cache
            $staleResponse = $this->getStaleResponse($fingerprint);
            if ($staleResponse !== null) {
                return $staleResponse;
            }

            // Re-throw if no stale cache available
            throw $e;
        }
    }

    /**
     * Cache a successful response for future stale serving.
     */
    private function cacheResponse(string $fingerprint, $response): void
    {
        $staleTtl = (int) env('STALE_CACHE_TTL', 604800); // Default 7 days

        try {
            $data = null;

            if (method_exists($response, 'getData')) {
                $data = $response->getData();
            } elseif (method_exists($response, 'getContent')) {
                $data = json_decode($response->getContent(), true);
            }

            if ($data !== null) {
                Cache::put(
                    self::STALE_CACHE_PREFIX . $fingerprint,
                    json_encode([
                        'data' => $data,
                        'status' => $response->getStatusCode(),
                        'cached_at' => time(),
                    ]),
                    $staleTtl
                );
            }
        } catch (\Exception $e) {
            // Caching failure should never break the response
        }
    }

    /**
     * Retrieve and serve a stale cached response.
     */
    private function getStaleResponse(string $fingerprint): ?\Illuminate\Http\JsonResponse
    {
        try {
            $cached = Cache::get(self::STALE_CACHE_PREFIX . $fingerprint);

            if ($cached === null) {
                return null;
            }

            $decoded = json_decode($cached, true);
            if ($decoded === null || !isset($decoded['data'])) {
                return null;
            }

            $cachedAt = $decoded['cached_at'] ?? 0;
            $age = time() - $cachedAt;

            return response()->json($decoded['data'], $decoded['status'] ?? 200)
                ->withHeaders([
                    'X-Jikan-Stale' => 'true',
                    'X-Jikan-Stale-Age' => $age,
                    'X-Jikan-Stale-Cached-At' => date('c', $cachedAt),
                    'Cache-Control' => 'public, max-age=60, stale-while-revalidate=3600',
                ]);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate a unique fingerprint for the request.
     */
    private function getFingerprint(Request $request): string
    {
        return sha1($request->getMethod() . ':' . $request->getRequestUri());
    }
}
