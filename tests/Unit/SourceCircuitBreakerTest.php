<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\SourceCircuitBreaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class SourceCircuitBreakerTest extends TestCase
{
    private SourceCircuitBreaker $breaker;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Carbon::setTestNow('2026-01-01 00:00:00');
        config()->set('jikan.source_circuit_breaker', [
            'failure_threshold' => 3,
            'cooldown_seconds' => 60,
            'failure_window_seconds' => 300,
        ]);
        $this->breaker = new SourceCircuitBreaker;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();

        parent::tearDown();
    }

    public function test_it_stays_closed_below_the_failure_threshold(): void
    {
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();

        $this->assertFalse($this->breaker->isOpen());
        $this->assertSame(2, $this->breaker->failureCount());
    }

    public function test_it_opens_at_the_threshold_and_reports_retry_after(): void
    {
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();

        $this->assertTrue($this->breaker->isOpen());
        $this->assertSame(60, $this->breaker->retryAfter());

        Carbon::setTestNow(now()->addSeconds(30));
        $this->assertTrue($this->breaker->isOpen());
        $this->assertSame(30, $this->breaker->retryAfter());
    }

    public function test_it_allows_a_probe_after_the_cooldown(): void
    {
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();

        Carbon::setTestNow(now()->addSeconds(61));

        $this->assertFalse($this->breaker->isOpen());
        $this->assertSame(0, $this->breaker->retryAfter());
    }

    public function test_success_resets_the_breaker(): void
    {
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();
        $this->breaker->recordFailure();
        $this->breaker->recordSuccess();

        $this->assertFalse($this->breaker->isOpen());
        $this->assertSame(0, $this->breaker->failureCount());
    }
}
