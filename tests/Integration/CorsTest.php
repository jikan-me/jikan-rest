<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use Tests\TestCase;

/**
 * CORS is opt-in (CORS_MIDDLEWARE, default off), matching the Lumen release,
 * so an edge proxy that already sets CORS headers is not duplicated.
 */
class CorsTest extends TestCase
{
    public function testNoCorsHeadersByDefault(): void
    {
        $response = $this->call('GET', '/v4/', [], [], [], ['HTTP_ORIGIN' => 'https://example.org']);

        $this->assertNull($response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testCorsHeadersWhenEnabled(): void
    {
        config(['cors.paths' => ['*']]);

        $response = $this->call('GET', '/v4/', [], [], [], ['HTTP_ORIGIN' => 'https://example.org']);

        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
