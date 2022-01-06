<?php

namespace App\Console\Commands\Indexer;

use App\Http\HttpHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jikan\Request\Genre\AnimeGenresRequest;
use Jikan\Request\Genre\MangaGenresRequest;

class GenreIndexer extends Command
{
    /**
     * The name and signature of the console command.
     *`
     * @var string
     */
    protected $signature = 'indexer:genres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index Anime & Manga Genres';

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
         * Anime Genres
         */
        echo "Indexing Anime Genres...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getAnimeGenres(new AnimeGenresRequest()),
                'json'
            ),
            true
        );

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $itemCount = count($results['genres']);
        echo "Parsed {$itemCount} anime genres\n";
        foreach ($results['genres'] as $i => $item) {
            $result = DB::table('genres_anime')
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

        $itemCount = count($results['explicit_genres']);
        echo "Parsed {$itemCount} anime explicit_genres\n";
        foreach ($results['explicit_genres'] as $i => $item) {
            $result = DB::table('explicit_genres_anime')
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

        $itemCount = count($results['themes']);
        echo "Parsed {$itemCount} anime themes\n";
        foreach ($results['themes'] as $i => $item) {
            $result = DB::table('themes_anime')
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

        $itemCount = count($results['demographics']);
        echo "Parsed {$itemCount} anime demographics\n";
        foreach ($results['demographics'] as $i => $item) {
            $result = DB::table('demographics_anime')
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
         * Manga Genres
         */
        echo "Indexing Manga Genres...\n";
        $results = \json_decode(
            app('SerializerV4')->serialize(
                app('JikanParser')
                    ->getMangaGenres(new MangaGenresRequest()),
                'json'
            ),
            true
        );

        if (HttpHelper::hasError($results)) {
            echo "FAILED: {$results->original['error']}\n";
            return;
        }

        $itemCount = count($results['genres']);
        echo "Parsed {$itemCount} manga genres\n";
        foreach ($results['genres'] as $i => $item) {
            $result = DB::table('genres_manga')
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

        $itemCount = count($results['explicit_genres']);
        echo "Parsed {$itemCount} manga explicit_genres\n";
        foreach ($results['explicit_genres'] as $i => $item) {
            $result = DB::table('explicit_genres_manga')
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

        $itemCount = count($results['themes']);
        echo "Parsed {$itemCount} manga themes\n";
        foreach ($results['themes'] as $i => $item) {
            $result = DB::table('themes_manga')
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

        $itemCount = count($results['demographics']);
        echo "Parsed {$itemCount} manga demographics\n";
        foreach ($results['demographics'] as $i => $item) {
            $result = DB::table('demographics_manga')
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
