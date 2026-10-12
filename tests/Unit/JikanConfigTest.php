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
                'AnimeController@main' => '86400',
                'UserController@profile' => 300,
                'GenreController@anime' => null,
                'MagazineController@main' => '',
            ],
        ]);

        $this->assertSame(86400, $config->cacheTtlForEndpoint('AnimeController@main'));
        $this->assertSame(300, $config->cacheTtlForEndpoint('UserController@profile'));
        $this->assertNull($config->cacheTtlForEndpoint('GenreController@anime'));
        $this->assertNull($config->cacheTtlForEndpoint('MagazineController@main'));
        $this->assertNull($config->cacheTtlForEndpoint('Unknown@endpoint'));
    }
}
