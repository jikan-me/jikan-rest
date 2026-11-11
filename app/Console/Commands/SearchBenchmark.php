<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class SearchBenchmark extends Command
{
    protected $signature = 'search:benchmark
                            {--base-url=http://127.0.0.1:8000 : API base URL}
                            {--queries= : Query corpus path}
                            {--check : Fail on relevance regression}
                            {--json : Emit machine-readable output}
                            {--when-ready : Run only when the anime catalogue reaches the threshold}
                            {--threshold=10000 : Minimum anime count for --when-ready}';

    protected $description = 'Run the lexical search relevance and latency benchmark';

    public function handle(): int
    {
        if ($this->option('when-ready')) {
            $count = (int) DB::table('anime')->count();
            $threshold = (int) $this->option('threshold');

            if ($count < $threshold) {
                $this->info("Search benchmark waiting: {$count}/{$threshold} anime indexed.");

                return 0;
            }

            $this->info("Search benchmark ready: {$count} anime indexed.");
        }

        $args = [
            PHP_BINARY,
            base_path('tools/search-benchmark/benchmark.php'),
            '--base-url='.$this->option('base-url'),
        ];

        if ($this->option('queries')) {
            $args[] = '--queries='.$this->option('queries');
        }
        if ($this->option('check')) {
            $args[] = '--check';
        }
        if ($this->option('json')) {
            $args[] = '--json';
        }

        $process = new Process($args);
        $process->setTimeout(600);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        return $process->getExitCode() ?? 1;
    }
}
