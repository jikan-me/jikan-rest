<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Events\SourceHeartbeatEvent;
use App\Providers\SourceHeartbeatProvider;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SourceHeartbeatProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The heartbeat collection accumulates events across the whole suite
        // (every integration request records one), so isolate each test.
        $this->clearHeartbeatCollections();
    }

    protected function tearDown(): void
    {
        $this->clearHeartbeatCollections();

        parent::tearDown();
    }

    private function clearHeartbeatCollections(): void
    {
        $db = DB::connection('mongodb')->getMongoDB();
        foreach (['failovers', 'failovers_meta'] as $collection) {
            $db->selectCollection($collection)->deleteMany([]);
        }
    }

    public function test_heartbeat_score_is_ratio_of_good_to_all_events_within_window(): void
    {
        SourceHeartbeatProvider::recordEvent(200, SourceHeartbeatEvent::GOOD_HEALTH);
        SourceHeartbeatProvider::recordEvent(200, SourceHeartbeatEvent::GOOD_HEALTH);
        SourceHeartbeatProvider::recordEvent(200, SourceHeartbeatEvent::GOOD_HEALTH);
        SourceHeartbeatProvider::recordEvent(503, SourceHeartbeatEvent::BAD_HEALTH);

        $this->assertEquals(0.75, SourceHeartbeatProvider::getHeartbeatScore());
        $this->assertCount(4, SourceHeartbeatProvider::getRecentEvents());
    }

    public function test_heartbeat_score_is_zero_when_no_events_recorded(): void
    {
        // float 0.0 (the declared return type coerces; JSON encodes it as 0)
        $this->assertSame(0.0, SourceHeartbeatProvider::getHeartbeatScore());
    }

    public function test_prune_fails_drops_events_outside_recency_window(): void
    {
        config()->set('jikan.source_bad_health_range', 30);
        config()->set('jikan.source_bad_health_max_store', 50);

        $now = time();
        $fails = [
            [$now - 100, 200, SourceHeartbeatEvent::GOOD_HEALTH], // too old
            [$now - 5, 200, SourceHeartbeatEvent::GOOD_HEALTH],
            [$now - 3, 500, SourceHeartbeatEvent::BAD_HEALTH],
            [$now, 200, SourceHeartbeatEvent::GOOD_HEALTH],
        ];

        $pruned = SourceHeartbeatProvider::pruneFails($fails);

        $this->assertCount(3, $pruned);
        $this->assertSame($now - 5, (int) $pruned[0][0]);
        $this->assertSame($now, (int) $pruned[2][0]);
    }

    public function test_prune_fails_caps_the_sample_at_max_store(): void
    {
        config()->set('jikan.source_bad_health_range', 30);
        config()->set('jikan.source_bad_health_max_store', 5);

        $now = time();
        $fails = [];
        // chronological order (oldest first), matching Mongo natural order
        for ($i = 19; $i >= 0; $i--) {
            $fails[] = [$now - $i, 200, SourceHeartbeatEvent::GOOD_HEALTH];
        }

        $pruned = SourceHeartbeatProvider::pruneFails($fails);

        $this->assertCount(5, $pruned);
        // keeps the most recent (last) entries
        $this->assertSame($now, (int) $pruned[4][0]);
    }

    public function test_prune_fails_is_safe_with_malformed_entries(): void
    {
        config()->set('jikan.source_bad_health_range', 30);

        $now = time();
        $pruned = SourceHeartbeatProvider::pruneFails([
            null,
            [1, 2],
            [$now, 200, SourceHeartbeatEvent::GOOD_HEALTH],
            ['not', 'an', 'event'],
        ]);

        $this->assertCount(1, $pruned);
        $this->assertSame($now, (int) $pruned[0][0]);
    }

    public function test_heartbeat_status_maps_score_buckets(): void
    {
        $this->assertSame('UNHEALTHY', SourceHeartbeatProvider::getHeartbeatStatus());

        // 5 good of 8 = 0.625 -> LEARNING (0.5 < score < source_good_health_score)
        for ($i = 0; $i < 5; $i++) {
            SourceHeartbeatProvider::recordEvent(200, SourceHeartbeatEvent::GOOD_HEALTH);
        }
        for ($i = 0; $i < 3; $i++) {
            SourceHeartbeatProvider::recordEvent(503, SourceHeartbeatEvent::BAD_HEALTH);
        }
        $this->assertSame('LEARNING', SourceHeartbeatProvider::getHeartbeatStatus());

        // all good -> HEALTHY (score 1.0 >= source_good_health_score)
        $this->clearHeartbeatCollections();
        for ($i = 0; $i < 3; $i++) {
            SourceHeartbeatProvider::recordEvent(200, SourceHeartbeatEvent::GOOD_HEALTH);
        }
        $this->assertSame('HEALTHY', SourceHeartbeatProvider::getHeartbeatStatus());
    }

    public function test_failover_lock_and_last_downtime_round_trip_through_mongo(): void
    {
        $this->assertFalse(SourceHeartbeatProvider::isFailoverEnabled());
        $this->assertSame(0, SourceHeartbeatProvider::getLastDowntime());
        $this->assertSame(0, SourceHeartbeatProvider::getFailoverLockTimestamp());

        SourceHeartbeatProvider::setFailoverLock(12345);
        SourceHeartbeatProvider::setLastDowntime(98765);

        $this->assertTrue(SourceHeartbeatProvider::isFailoverEnabled());
        $this->assertSame(12345, SourceHeartbeatProvider::getFailoverLockTimestamp());
        $this->assertSame(98765, SourceHeartbeatProvider::getLastDowntime());

        SourceHeartbeatProvider::clearFailoverLock();

        $this->assertFalse(SourceHeartbeatProvider::isFailoverEnabled());
        $this->assertSame(0, SourceHeartbeatProvider::getFailoverLockTimestamp());
    }
}
