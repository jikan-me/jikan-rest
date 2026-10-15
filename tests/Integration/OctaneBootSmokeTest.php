<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Boots the actual Octane (RoadRunner) server and smoke-checks `/up`.
 * Self-skips when the `rr` binary isn't present (it's gitignored; CI would
 * `octane:install` first), keeping fresh-checkout runs green.
 */
final class OctaneBootSmokeTest extends TestCase
{
    private $proc;

    protected function setUp(): void
    {
        parent::setUp();

        if (! is_file(base_path('rr'))) {
            $this->markTestSkipped('RoadRunner binary not present — run octane:install or install.sh.');
        }
    }

    public function test_octane_roadrunner_boots_and_serves_up(): void
    {
        $port = 8105;
        $cmd = [PHP_BINARY, 'artisan', 'octane:start', '--server=roadrunner',
                '--host=127.0.0.1', '--port='.$port, '--workers=1'];
        $pipes = [];
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
        $this->proc = $proc;

        try {
            $ok = false;
            for ($i = 0; $i < 20; $i++) {
                $code = @file_get_contents("http://127.0.0.1:{$port}/up");
                if ($code === 'OK') {
                    $ok = true;
                    break;
                }
                usleep(500_000);
            }
            $this->assertTrue($ok, 'Octane /up did not respond within ~10s');
        } finally {
            if (is_resource($proc)) {
                proc_terminate($proc);
                proc_close($proc);
            }
            @unlink(base_path('.rr.yaml'));
        }
    }

    protected function tearDown(): void
    {
        if (is_resource($this->proc)) {
            proc_terminate($this->proc);
            proc_close($this->proc);
        }
        parent::tearDown();
    }
}