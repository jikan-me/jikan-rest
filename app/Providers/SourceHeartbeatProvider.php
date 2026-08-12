<?php

namespace App\Providers;

use App\Events\SourceHeartbeatEvent;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class SourceHeartbeatProvider extends ServiceProvider
{

    const BAD_HEALTH_STATUSES = [403, 500, 501, 502, 503, 504, 505];

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SourceHeartbeatEvent' => [
            'App\Listeners\SourceHeartbeatListener',
        ],
    ];

    public static function isFailoverEnabled() : bool
    {
        return Storage::exists('source_failover.lock');
    }

    public static function getLastDowntime() : int
    {
        try {
            return Storage::lastModified('source_failover_last_downtime');
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getHeartbeatScore() : float
    {
        try {
            $failsJson = Storage::get('failovers.json');
            $fails = $failsJson ? json_decode($failsJson, true) : [];
        } catch (\Exception $e) {
            $fails = [];
        }

        // remove any fails greater than SOURCE_BAD_HEALTH_RANGE
        foreach ($fails as $fail) {

            if ($fail[0] >= (time()-env('SOURCE_BAD_HEALTH_RANGE'))) {
                unset($fail);
            }
        }

        // slice
        if (count($fails) > env('SOURCE_BAD_HEALTH_MAX_STORE')) {
            $fails = array_slice($fails, 0 - env('SOURCE_BAD_HEALTH_MAX_STORE'));
        }

        $score = 0;
        $totalFails = count($fails) - 1;

        foreach ($fails as $fail) {
            if ((int) $fail[2] === SourceHeartbeatEvent::GOOD_HEALTH) {
                $score++;
            }
        }

        return $score / max($totalFails, 1);
    }

    public static function getHeartbeatStatus() : string
    {
        $score = self::getHeartbeatScore();

        if ($score > 0.5 && $score < env('SOURCE_GOOD_HEALTH_SCORE')) {
            return "LEARNING";
        }

        if ($score <= 0.5) {
            return "UNHEALTHY";
        }

        return "HEALTHY";
    }

    /**
     * Determine if scraping should be attempted based on current MAL health.
     *
     * Returns false (skip scraping) when:
     * - Failover mode is active (lock file exists)
     * - Health score is below the critical threshold (< 0.3)
     *
     * This implements the circuit breaker pattern to avoid hammering
     * an already-struggling upstream.
     */
    public static function shouldAttemptScrape(): bool
    {
        if (self::isFailoverEnabled()) {
            return false;
        }

        try {
            $score = self::getHeartbeatScore();
            // Below 30% success rate — MAL is likely down, skip scraping
            return $score >= 0.3;
        } catch (\Exception $e) {
            // If we can't check health, default to allowing scrape attempts
            return true;
        }
    }

    /**
     * Record a bad health event from the scraper.
     * Convenience method for external callers.
     */
    public static function recordBadHealth(int $statusCode): void
    {
        try {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $statusCode));
        } catch (\Exception $e) {
            // Swallow - health recording should never break the caller
        }
    }

    /**
     * Record a good health event from the scraper.
     * Convenience method for external callers.
     */
    public static function recordGoodHealth(): void
    {
        try {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::GOOD_HEALTH, 200));
        } catch (\Exception $e) {
            // Swallow
        }
    }
}
