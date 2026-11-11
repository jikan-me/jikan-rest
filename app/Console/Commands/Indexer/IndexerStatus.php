<?php

namespace App\Console\Commands\Indexer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IndexerStatus extends Command
{
    protected $signature = 'indexer:status {--json : Emit machine-readable output}';

    protected $description = 'Show anime indexer progress, failures and stored count';

    public function handle(): int
    {
        $ids = Storage::exists('indexer/anime_mal_id.json')
            ? json_decode((string) Storage::get('indexer/anime_mal_id.json'), true)
            : [];
        $total = is_array($ids) ? count($ids) : 0;
        $position = Storage::exists('indexer/indexer_anime.save')
            ? (int) Storage::get('indexer/indexer_anime.save')
            : null;
        $failed = Storage::exists('indexer/indexer_anime.failed')
            ? count((array) json_decode((string) Storage::get('indexer/indexer_anime.failed'), true))
            : 0;
        $stored = (int) DB::table('anime')->count();

        $status = [
            'total_mal_ids' => $total,
            'current_index' => $position,
            'processed' => $position === null ? null : $position + 1,
            'remaining' => $position === null ? null : max(0, $total - $position - 1),
            'failed_ids' => $failed,
            'stored_anime' => $stored,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return 0;
        }

        $this->table(['Metric', 'Value'], [
            ['MAL IDs available', $total],
            ['Current index', $position ?? 'not running / complete'],
            ['Processed', $status['processed'] ?? '-'],
            ['Remaining', $status['remaining'] ?? '-'],
            ['Failed IDs', $failed],
            ['Stored anime', $stored],
        ]);

        return 0;
    }
}
