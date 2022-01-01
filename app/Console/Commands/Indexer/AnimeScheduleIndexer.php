<?php

namespace App\Console\Commands\Indexer;

use App\Http\HttpHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Schedule\ScheduleRequest;


class AnimeScheduleIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     * @var string
     */
    protected $signature = 'indexer:anime-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index anime schedule';

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

        echo "Note: AnimeScheduleIndexer makes sure anime currently airing are upto update so the schedules endpoint returns fresh information\n\n";

        /**
         * Schedule
         */
        echo "Fetching Schedule...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getSchedule(new ScheduleRequest()),
                'json'
            ),
            true
        );

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $anime = [];

        foreach ($results as $day) {
            foreach ($day as $entry) {
                $anime[] = $entry;
            }
        }

        $i = 1;
        $itemCount = count($anime);
        echo "Anime currently airing: {$itemCount} entries\n";
        foreach ($anime as $entry) {
            $url = env('APP_URL') . "/v4/anime/{$entry['mal_id']}";

            file_get_contents($url);
            sleep(3); // prevent rate-limit

            echo "Updating {$i}/{$itemCount} \r";
            try {
            } catch (\Exception $e) {
                echo "[SKIPPED] Failed to fetch {$url}";
            }
            $i++;
        }

        echo str_pad("Indexing complete", 10).PHP_EOL;
    }
}
