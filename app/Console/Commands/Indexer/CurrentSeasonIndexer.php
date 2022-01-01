<?php

namespace App\Console\Commands\Indexer;

use App\Http\HttpHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Schedule\ScheduleRequest;
use Jikan\Request\Seasonal\SeasonalRequest;


class CurrentSeasonIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     * @var string
     */
    protected $signature = 'indexer:anime-current-season';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index anime in current season';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        echo "Note: CurrentSeasonIndexer makes sure anime in current season are upto update so the /seasons/now endpoint returns fresh information\n\n";

        /**
         * Current Season
         */
        echo "Fetching Current Season...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getSeasonal(new SeasonalRequest()),
                'json'
            ),
            true
        );

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $anime = $results['anime'];
        $itemCount = count($anime);
        echo "Anime in current season: {$itemCount} entries\n";
        foreach ($anime as $i => $entry) {
            $url = env('APP_URL') . "/v4/anime/{$entry['mal_id']}";

            file_get_contents($url);
            sleep(3); // prevent rate-limit

            echo "Updating {$i}/{$itemCount} {$url} [{$entry['mal_id']} - {$entry['title']}] \n";
            try {
            } catch (\Exception $e) {
                echo "[SKIPPED] Failed to fetch {$url}\n";
            }
        }

        echo str_pad("Indexing complete", 100).PHP_EOL;
    }
}
