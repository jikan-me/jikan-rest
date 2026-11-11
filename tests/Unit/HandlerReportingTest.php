<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Jikan\Exception\BadResponseException as JikanBadResponseException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Upstream 404s (a MAL "not found") must not be logged as errors; other upstream
 * failures (rate limit, 5xx, invalid codes) must still be reported.
 */
final class HandlerReportingTest extends TestCase
{
    private function handler(): ExceptionHandler
    {
        return $this->app->make(ExceptionHandler::class);
    }

    public function test_upstream_404_is_not_reported(): void
    {
        $this->assertFalse($this->handler()->shouldReport(new JikanBadResponseException('not found', 404)));
    }

    /** @return array<string, array{int}> */
    public static function reportedCodes(): array
    {
        return ['429' => [429], '403' => [403], '500' => [500], '503' => [503], '0' => [0]];
    }

    #[DataProvider('reportedCodes')]
    public function test_other_upstream_failures_are_still_reported(int $code): void
    {
        $this->assertTrue($this->handler()->shouldReport(new JikanBadResponseException('upstream failed', $code)));
    }

    public function test_unrelated_exceptions_are_still_reported(): void
    {
        $this->assertTrue($this->handler()->shouldReport(new \RuntimeException('boom', 404)));
    }
}
