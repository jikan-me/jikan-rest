<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Console\Commands\Indexer\HarvestIndexer;
use App\Console\Commands\Indexer\ItemsIndexer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\TestCase;

/**
 * The indexers fetch from the running API with a bounded wait (--timeout) instead
 * of a bare file_get_contents(), and a failed fetch is recorded, not counted as
 * success. Nothing here connects to a socket: app.url is not a URL, so every
 * fetch fails immediately (a closed local port would stall ~60s under WSL).
 */
final class IndexerFetchTimeoutTest extends TestCase
{
    /**
     * Runs a command and returns what it echoed (the indexers echo progress, which
     * Laravel's `artisan()` expectations cannot see).
     *
     * @param  array<string, mixed>  $arguments
     */
    private function runCapturingEcho(string $command, array $arguments): string
    {
        ob_start();
        try {
            $exit = Artisan::call($command, $arguments);
        } finally {
            $echoed = (string) ob_get_clean();
        }
        $this->assertSame(0, $exit);

        return $echoed;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake();
        config(['app.url' => '/nonexistent-jikan']);
    }

    public function testItemsIndexerRecordsUnreachableFetchesAsFailures(): void
    {
        Storage::put('indexer/indexer_anime.failed', json_encode([101, 102]));

        $out = $this->runCapturingEcho('indexer:items', ['family' => 'anime', '--failed' => true, '--delay' => 0, '--timeout' => 1]);

        $this->assertStringContainsString('[SKIPPED] Failed to fetch /nonexistent-jikan/v4/anime/101', $out);
        $this->assertStringContainsString('0 indexed; 2 failed', $out);
        $this->assertSame([101, 102], json_decode(Storage::get('indexer/indexer_anime.failed'), true));
    }

    public function testHarvestIndexerRecordsUnreachableFetchesAsFailures(): void
    {
        Storage::put('indexer/indexer_harvest_anime.failed', json_encode([201, 202]));

        $out = $this->runCapturingEcho('indexer:harvest', ['--kind' => 'anime', '--failed' => true, '--delay' => 0, '--timeout' => 1]);

        $this->assertStringContainsString('[SKIPPED] anime/201: unreachable /nonexistent-jikan/v4/anime/201/characters', $out);
        $this->assertStringContainsString('0 harvested; 2 failed', $out);
    }

    /**
     * @return array<string, array{class-string, ?string, int}>
     */
    public static function timeoutOptions(): array
    {
        return [
            'items default' => [ItemsIndexer::class, null, 30],
            'items custom' => [ItemsIndexer::class, '7', 7],
            'items non-positive falls back' => [ItemsIndexer::class, '0', 30],
            'harvest default' => [HarvestIndexer::class, null, 30],
            'harvest custom' => [HarvestIndexer::class, '5', 5],
        ];
    }

    #[DataProvider('timeoutOptions')]
    public function testStreamContextCarriesTheConfiguredTimeout(string $class, ?string $option, int $expected): void
    {
        $command = new $class;
        $input = $class === ItemsIndexer::class ? ['family' => 'anime'] : [];
        if ($option !== null) {
            $input['--timeout'] = $option;
        }
        $inputProperty = new \ReflectionProperty(Command::class, 'input');
        $inputProperty->setValue($command, new ArrayInput($input, $command->getDefinition()));

        $method = new \ReflectionMethod($class, 'fetchContext');
        $options = stream_context_get_options($method->invoke($command));

        $this->assertSame($expected, $options['http']['timeout']);
    }
}
