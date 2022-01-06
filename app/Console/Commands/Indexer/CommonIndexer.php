<?php

namespace App\Console\Commands\Indexer;

use App\Http\HttpHelper;
use App\Http\HttpResponse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenresRequest;
use Jikan\Request\Magazine\MagazinesRequest;
use Jikan\Request\Producer\ProducersRequest;
use Jikan\Request\SeasonList\SeasonListRequest;

class CommonIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     * @var string
     */
    protected $signature = 'indexer:common';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index common endpoint metadata: Producers, Magazines';

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

        echo "Note: If an entry already exists, it will be updated instead.\n\n";

        /**
         * Producers
         */
        echo "Indexing Producers...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getProducers(new ProducersRequest()),
                'json'
            ),
            true
        )['producers'];

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $itemCount = count($results);
        echo "Parsed {$itemCount} producers\n";
        foreach ($results as $i => $item) {
            $result = DB::table('producers')
                ->updateOrInsert(
                    [
                        'mal_id' => $item['mal_id']
                    ],
                    [
                        'mal_id' => $item['mal_id'],
                        'name' => $item['name'],
                        'url' => $item['url'],
                        'count' => $item['count']
                    ]
                );
            echo "Indexing {$i}/{$itemCount} \r";
        }

        /**
         * Magazines
         */
        echo "Indexing Magazines...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getMagazines(new MagazinesRequest()),
                'json'
            ),
            true
        )['magazines'];

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $itemCount = count($results);
        echo "Parsed {$itemCount} magazines\n";
        foreach ($results as $i => $item) {
            $result = DB::table('magazines')
                ->updateOrInsert(
                    [
                        'mal_id' => $item['mal_id']
                    ],
                    [
                        'mal_id' => $item['mal_id'],
                        'name' => $item['name'],
                        'url' => $item['url'],
                        'count' => $item['count']
                    ]
                );
            echo "Indexing {$i}/{$itemCount} \r";
        }

        echo str_pad("Indexing complete", 10).PHP_EOL;
    }
}
