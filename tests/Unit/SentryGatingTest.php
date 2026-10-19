<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Master only registered Sentry when REPORTING=true (and the driver is sentry).
 * Auto-discovery is disabled in composer.json so that contract holds again.
 */
final class SentryGatingTest extends TestCase
{
    public function test_sentry_is_not_registered_by_default(): void
    {
        $this->assertFalse(config('jikan.reporting'));
        $this->assertFalse($this->app->bound('sentry'));
        $this->assertNull($this->app->getProvider(\Sentry\Laravel\ServiceProvider::class));
    }

    public function test_sentry_is_not_part_of_package_auto_discovery(): void
    {
        $discovered = array_keys($this->app->make(\Illuminate\Foundation\PackageManifest::class)->config('providers') ?? []);

        $this->assertNotContains(\Sentry\Laravel\ServiceProvider::class, $discovered);
        $this->assertNotContains('sentry/sentry-laravel', array_keys($this->app->make(\Illuminate\Foundation\PackageManifest::class)->manifest));
    }
}
