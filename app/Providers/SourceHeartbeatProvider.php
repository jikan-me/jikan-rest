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
            return Storage::lastModified('source_failover.lock');
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getHeartbeatScore() : float
    {
        try {
            $failsJson = Storage::get('failovers.json');
            $fails = json_decode($failsJson, true);
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

        $scored = $score / max($totalFails, 1);

        return $scored;
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
}
