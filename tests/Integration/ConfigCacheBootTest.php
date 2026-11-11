<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Guard: `php artisan config:cache` must build and the app must keep serving
 * after booting from the generated cache (env() usage outside config would
 * silently null there). Always clears the cache afterwards so sibling tests
 * are unaffected.
 */
final class ConfigCacheBootTest extends TestCase
{
    public function test_config_cache_builds_and_app_still_serves(): void
    {
        try {
            $code = Artisan::call('config:cache');
            $this->assertSame(0, $code, 'config:cache must exit 0');

            // config:cache only writes the cache file; the current container keeps
            // the already-loaded configuration. Reboot so the assertions exercise
            // an actual cached-config boot instead of the pre-cache container.
            $this->refreshApplication();

            $this->assertTrue($this->app->configurationIsCached(), 'application must boot from cached config');
            $this->assertNotEmpty(config('app.name'), 'config survives after config:cache');
            $this->get('/up')->assertOk()->assertSeeText('OK');
        } finally {
            Artisan::call('config:clear');
        }
    }
}
