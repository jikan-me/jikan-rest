<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The v4 limiter is opt-in (RATE_LIMIT_PER_MINUTE, default 0) and keys on
 * request()->ip(), which is only the real client address behind a proxy when
 * the proxy is trusted (TRUSTED_PROXIES).
 */
class RateLimitTest extends TestCase
{
    private const PROXY = '172.18.0.5';

    protected function tearDown(): void
    {
        TrustProxies::flushState();
        parent::tearDown();
    }

    public function testLimiterIsDisabledByDefault(): void
    {
        $response = $this->requestFrom('203.0.113.1');

        $this->assertNull($response->headers->get('X-RateLimit-Limit'));
        $this->assertNotSame(429, $response->getStatusCode());
    }

    public function testClientsBehindATrustedProxyGetSeparateBuckets(): void
    {
        config(['jikan.rate_limit_per_minute' => 2]);
        TrustProxies::at('*');

        $this->assertSame('1', $this->requestFrom('203.0.113.1')->headers->get('X-RateLimit-Remaining'));
        $this->assertSame('0', $this->requestFrom('203.0.113.1')->headers->get('X-RateLimit-Remaining'));
        $this->assertSame('1', $this->requestFrom('203.0.113.2')->headers->get('X-RateLimit-Remaining'));
        $this->assertSame(429, $this->requestFrom('203.0.113.1')->getStatusCode());
        $this->assertSame(200, $this->requestFrom('203.0.113.2')->getStatusCode());
    }

    public function testWithoutTrustedProxiesEveryClientSharesTheProxyBucket(): void
    {
        config(['jikan.rate_limit_per_minute' => 2]);

        $this->requestFrom('203.0.113.1');
        $this->requestFrom('203.0.113.2');

        $this->assertSame(429, $this->requestFrom('203.0.113.3')->getStatusCode());
    }

    private function requestFrom(string $clientIp): TestResponse
    {
        return $this->call('GET', '/v4/', [], [], [], [
            'REMOTE_ADDR' => self::PROXY,
            'HTTP_X_FORWARDED_FOR' => $clientIp,
        ]);
    }
}
