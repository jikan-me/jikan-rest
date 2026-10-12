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
        // nothing listens on this port, so every fetch fails fast and offline
        config(['app.url' => 'http://127.0.0.1:9']);
        Storage::put('indexer/incremental/anime_failed.json', json_encode([101, 102, 103]));

        $this->artisan('indexer:incremental', ['mediaType' => ['anime'], '--failed' => true])
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
}
