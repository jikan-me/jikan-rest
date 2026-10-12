<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\JikanConfig;
use PHPUnit\Framework\TestCase;

final class JikanConfigTest extends TestCase
{
    public function test_per_endpoint_cache_ttls_are_normalised_to_int_or_null(): void
    {
        $config = new JikanConfig([
            'per_endpoint_cache_ttl' => [
                'v4.anime.main' => '86400',
                'v4.user.profile' => 300,
                'v4.genre.anime' => null,
                'v4.magazine.main' => '',
            ],
        ]);

        $this->assertSame(86400, $config->cacheTtlForEndpoint('v4.anime.main'));
        $this->assertSame(300, $config->cacheTtlForEndpoint('v4.user.profile'));
        $this->assertNull($config->cacheTtlForEndpoint('v4.genre.anime'));
        $this->assertNull($config->cacheTtlForEndpoint('v4.magazine.main'));
        $this->assertNull($config->cacheTtlForEndpoint('v4.unknown.endpoint'));
    }
}
