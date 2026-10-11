<?php

namespace App\Providers;

use App\Events\SourceHeartbeatEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use MongoDB\Collection;
use MongoDB\Database;

/**
 * Source-heartbeat (MAL availability) storage & scoring.
 *
 * Heartbeat events are stored in the MongoDB "failovers" collection with an
 * atomic insertOne per request (previously a non-atomic read-modify-write of a
 * failovers.json file, which raced under Octane multi-worker). The failover
 * lock and the last-downtime marker live in the "failovers_meta" collection
 * (one small doc per key). Reads apply the recency window + size cap through
 * pruneFails(); every accessor goes to storage per call, so there is no
 * in-memory state that could leak between Octane requests.
 */
class SourceHeartbeatProvider extends ServiceProvider
{
    const BAD_HEALTH_STATUSES = [403, 500, 501, 502, 503, 504, 505];

    /**
     * The event listener mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        'App\Events\SourceHeartbeatEvent' => [
            'App\Listeners\SourceHeartbeatListener',
        ],
    ];

    public static function isFailoverEnabled(): bool
    {
        return self::failoverMetaCollection()->findOne(['_id' => 'lock']) !== null;
    }

    public static function getLastDowntime(): int
    {
        $doc = self::failoverMetaCollection()->findOne(['_id' => 'last_downtime']);

        return $doc && isset($doc['timestamp']) ? (int) $doc['timestamp'] : 0;
    }

    /**
     * Atomically record a single heartbeat event, then drop events outside the
     * recency window so the collection cannot grow unbounded. The read path
     * applies the same window again via pruneFails().
     */
    public static function recordEvent(int $status, int $health): void
    {
        self::heartbeatCollection()->insertOne([
            'timestamp' => time(),
            'status' => $status,
            'health' => $health,
        ]);

        self::heartbeatCollection()->deleteMany([
            'timestamp' => ['$lt' => time() - config('jikan.source_bad_health_range')],
        ]);
    }

    /**
     * Read recorded heartbeat events as [timestamp, status, health] arrays
     * (recency window + size cap applied in-memory).
     *
     * @return array[]
     */
    /**
     * @return array<int, array{0: int, 1: int, 2: int}>
     */
    public static function getRecentEvents(): array
    {
        $cursor = self::heartbeatCollection()->find(
            [],
            ['projection' => ['_id' => 0, 'timestamp' => 1, 'status' => 1, 'health' => 1]]
        );

        $events = [];
        foreach ($cursor as $doc) {
            $events[] = [
                (int) ($doc['timestamp'] ?? 0),
                (int) ($doc['status'] ?? 0),
                (int) ($doc['health'] ?? SourceHeartbeatEvent::BAD_HEALTH),
            ];
        }

        return self::pruneFails($events);
    }

    public static function setFailoverLock(int $timestamp): void
    {
        self::failoverMetaCollection()->updateOne(
            ['_id' => 'lock'],
            ['$set' => ['timestamp' => $timestamp]],
            ['upsert' => true]
        );
    }

    public static function clearFailoverLock(): void
    {
        self::failoverMetaCollection()->deleteOne(['_id' => 'lock']);
    }

    public static function getFailoverLockTimestamp(): int
    {
        $doc = self::failoverMetaCollection()->findOne(['_id' => 'lock']);

        return $doc && isset($doc['timestamp']) ? (int) $doc['timestamp'] : 0;
    }

    public static function setLastDowntime(int $timestamp): void
    {
        self::failoverMetaCollection()->updateOne(
            ['_id' => 'last_downtime'],
            ['$set' => ['timestamp' => $timestamp]],
            ['upsert' => true]
        );
    }

    public static function clearEvents(): void
    {
        self::heartbeatCollection()->deleteMany([]);
    }

    public static function getHeartbeatScore(): float
    {
        $fails = self::getRecentEvents();

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
     * @param array<int, array{0: int, 1: int, 2: int}> $fails  entries as [timestamp, status, health]
     * @return array<int, array{0: int, 1: int, 2: int}>
     */
    public static function pruneFails(array $fails): array
    {
        // remove events older than SOURCE_BAD_HEALTH_RANGE (keeps recent ones)
        $cutoff = time() - config('jikan.source_bad_health_range');

        $fails = array_values(array_filter(
            $fails,
            fn (array $fail): bool => (int) $fail[0] >= $cutoff
        ));

        // cap the sample at SOURCE_BAD_HEALTH_MAX_STORE
        if (count($fails) > config('jikan.source_bad_health_max_store')) {
            $fails = array_slice($fails, 0 - config('jikan.source_bad_health_max_store'));
        }

        return $fails;
    }

    public static function getHeartbeatStatus(): string
    {
        $score = self::getHeartbeatScore();

        if ($score > 0.5 && $score < config('jikan.source_good_health_score')) {
            return 'LEARNING';
        }

        if ($score <= 0.5) {
            return 'UNHEALTHY';
        }

        return 'HEALTHY';
    }

    private static function heartbeatCollection(): Collection
    {
        return self::database()->selectCollection('failovers');
    }

    private static function failoverMetaCollection(): Collection
    {
        return self::database()->selectCollection('failovers_meta');
    }

    private static function database(): Database
    {
        return DB::connection('mongodb')->getMongoDB();
    }
}
