<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\CachedData;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ResponseJikanCacheFlagsTest extends TestCase
{
    public function test_fallback_responses_expose_cache_headers(): void
    {
        Carbon::setTestNow('2026-01-01 00:00:00');
        $data = CachedData::fromArray([
            'modifiedAt' => Carbon::now()->getTimestamp(),
            'data' => ['ok' => true],
        ])->asFallback(30);

        $response = response()->json(['data' => ['ok' => true]])
            ->addJikanCacheFlags('fingerprint', $data);

        $this->assertSame('1', $response->headers->get('X-Jikan-Cached'));
        $this->assertSame('30', $response->headers->get('Retry-After'));

        Carbon::setTestNow();
    }
}
