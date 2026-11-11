<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Boots the actual Octane server on Swoole and smoke-checks `/up`.
 * Self-skips when ext-swoole isn't loaded (fresh local checkouts); CI loads
 * the extension in the main test job, so it runs for real there.
 */
final class OctaneBootSmokeTest extends TestCase
{
    /** @var resource|null */
    private $proc;

    protected function setUp(): void
    {
        parent::setUp();

        if (!extension_loaded('swoole') && !extension_loaded('openswoole')) {
            $this->markTestSkipped('ext-swoole is not loaded — install it (pecl install swoole) to boot Octane.');
        }
    }

    public function test_octane_swoole_boots_and_serves_up(): void
    {
        $port = 8105;
        $stateFile = sys_get_temp_dir().'/jikan-octane-smoke-'.getmypid().'.json';
        $cmd = [PHP_BINARY, 'artisan', 'octane:start', '--server=swoole',
            '--host=127.0.0.1', '--port='.$port, '--workers=1', '--task-workers=1'];
        $env = getenv() + ['OCTANE_STATE_FILE' => $stateFile];
        $pipes = [];
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path(), $env);
        $this->proc = $proc;

        try {
            $body = false;
            for ($i = 0; $i < 40; $i++) {
                $body = @file_get_contents("http://127.0.0.1:{$port}/up");
                if ($body === 'OK') {
                    break;
                }
                usleep(250_000);
            }
            $this->assertSame('OK', $body, 'Octane (Swoole) /up did not respond within ~10s');
        } finally {
            $this->stop();
            @unlink($stateFile);
        }
    }

    private function stop(): void
    {
        if (is_resource($this->proc)) {
            // SIGTERM lets the Swoole master stop its workers and free the port.
            proc_terminate($this->proc, 15);
            proc_close($this->proc);
        }
        $this->proc = null;
    }

    protected function tearDown(): void
    {
        $this->stop();
        parent::tearDown();
    }
}
