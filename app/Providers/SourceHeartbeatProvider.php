<?php

namespace App\Providers;

use App\Events\SourceHeartbeatEvent;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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

        $fails = self::pruneFails($fails);

        $score = 0;
        $totalFails = count($fails);

        foreach ($fails as $fail) {
            if ((int) $fail[2] === SourceHeartbeatEvent::GOOD_HEALTH) {
                $score++;
            }
        }

        return $score / max($totalFails, 1);
    }

    /**
     * Drop heartbeats outside the recency window and cap the stored sample.
     *
     * @param  array[]  $fails  entries as [timestamp, status, health]
     * @return array[]
     */
    public static function pruneFails(array $fails): array
    {
        // remove events older than SOURCE_BAD_HEALTH_RANGE (keeps recent ones)
        $cutoff = time() - config('jikan.source_bad_health_range');

        $fails = array_values(array_filter(
            $fails,
            fn ($fail): bool => is_array($fail) && isset($fail[0]) && (int) $fail[0] >= $cutoff
        ));

        // cap the sample at SOURCE_BAD_HEALTH_MAX_STORE
        if (count($fails) > config('jikan.source_bad_health_max_store')) {
            $fails = array_slice($fails, 0 - config('jikan.source_bad_health_max_store'));
        }

        return $fails;
    }

    public static function getHeartbeatStatus() : string
    {
        $score = self::getHeartbeatScore();

        if ($score > 0.5 && $score < config('jikan.source_good_health_score')) {
            return "LEARNING";
        }

        if ($score <= 0.5) {
            return "UNHEALTHY";
        }

        return "HEALTHY";
    }
}
