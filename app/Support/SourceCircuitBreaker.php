<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache-backed circuit breaker for upstream MAL outages/rate limits.
 *
 * State is shared when the cache store is shared (Redis); with the file store
 * it is per-worker. This class does not perform requests itself.
 */
final class SourceCircuitBreaker
{
    public function isOpen(string $source = 'mal'): bool
    {
        $openedAt = Cache::get($this->key($source).':opened_at');

        if ($openedAt === null) {
            return false;
        }

        $remaining = $this->retryAfter($source);
        if ($remaining <= 0) {
            // Cooldown elapsed: allow the next request through as a probe.
            Cache::forget($this->key($source).':opened_at');

            return false;
        }

        return true;
    }

    public function retryAfter(string $source = 'mal'): int
    {
        $openedAt = (int) Cache::get($this->key($source).':opened_at', 0);
        if ($openedAt === 0) {
            return 0;
        }

        $elapsed = max(0, now()->timestamp - $openedAt);

        return max(0, $this->cooldown() - $elapsed);
    }

    public function recordFailure(string $source = 'mal'): void
    {
        $failures = $this->failureCount($source) + 1;
        Cache::put($this->key($source).':failures', $failures, now()->addSeconds($this->failureWindow()));

        if ($failures >= $this->threshold()) {
            Cache::put($this->key($source).':opened_at', now()->timestamp, now()->addSeconds($this->cooldown()));
        }
    }

    public function recordSuccess(string $source = 'mal'): void
    {
        Cache::forget($this->key($source).':failures');
        Cache::forget($this->key($source).':opened_at');
    }

    public function failureCount(string $source = 'mal'): int
    {
        return (int) Cache::get($this->key($source).':failures', 0);
    }

    private function key(string $source): string
    {
        return 'source-circuit:'.$source;
    }

    private function threshold(): int
    {
        return max(1, (int) config('jikan.source_circuit_breaker.failure_threshold', 3));
    }

    private function cooldown(): int
    {
        return max(1, (int) config('jikan.source_circuit_breaker.cooldown_seconds', 60));
    }

    private function failureWindow(): int
    {
        return max(1, (int) config('jikan.source_circuit_breaker.failure_window_seconds', 300));
    }
}
