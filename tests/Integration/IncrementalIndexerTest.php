<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncrementalIndexerTest extends TestCase
{
    public function testFailedOptionRetriesEveryRecordedFailure(): void
    {
        Storage::fake();
        // not a URL: every fetch fails immediately and offline. (A closed local port is NOT used:
        // under WSL, connecting to one stalls for the whole 60s default_socket_timeout.)
        config(['app.url' => '/nonexistent-jikan']);
        Storage::put('indexer/incremental/anime_failed.json', json_encode([101, 102, 103]));

        $this->artisan('indexer:incremental', ['mediaType' => ['anime'], '--failed' => true, '--delay' => 1])
            ->expectsOutputToContain('3 anime entries available')
            ->expectsOutputToContain('Indexing/Updating 3/3')
            ->assertExitCode(0);
    }

    public function testResumeAndFailedCannotBeCombined(): void
    {
        $this->artisan('indexer:incremental', ['mediaType' => ['anime'], '--failed' => true, '--resume' => true])
            ->expectsOutputToContain('cannot be used together')
            ->assertExitCode(1);
    }

    public function testDelayOptionPausesBetweenRequests(): void
    {
        Storage::fake();
        config(['app.url' => '/nonexistent-jikan']);
        Storage::put('indexer/incremental/anime_failed.json', json_encode([101, 102, 103]));

        $start = microtime(true);
        $this->artisan('indexer:incremental', ['mediaType' => ['anime'], '--failed' => true, '--delay' => 1])
            ->assertExitCode(0);

        // three ids, one second between them (none after the last)
        $this->assertGreaterThanOrEqual(1.9, microtime(true) - $start);
    }
}
