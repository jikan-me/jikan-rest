<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Support\HttpHelper;
use App\Support\JikanConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The random endpoints must never be served from the micro-cache (every call has to
 * return a fresh entry); everything else is cached. This regressed once during the
 * Lumen -> Laravel port when the route introspection silently stopped matching.
 */
class MicroCachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['jikan.micro_caching_enabled' => true]);
        $this->app->singleton(JikanConfig::class, fn () => new JikanConfig(config('jikan')));
        Cache::flush();
    }

    public function testRandomEndpointsAreNotMicroCached(): void
    {
        $this->get('/v4/random/anime');

        $this->assertFalse(Cache::has($this->fingerprint('/v4/random/anime')));
    }

    public function testOtherEndpointsAreMicroCached(): void
    {
        $this->get('/v4/genres/anime')->assertStatus(200);

        $this->assertTrue(Cache::has($this->fingerprint('/v4/genres/anime')));
    }

    private function fingerprint(string $uri): string
    {
        return sha1(HttpHelper::resolveRequestFingerprint(Request::create($uri)));
    }
}
