<?php

namespace App\Testing;

use Illuminate\Support\Facades\DB;

/**
 * A trait for test cases which want to clear the database after each test
 * @codeCoverageIgnore
 */
trait SyntheticMongoDbTransaction
{
    private static array $jikanModels = [];

    private static function getJikanModels(): array
    {
        if (count(self::$jikanModels) === 0)
        {
            self::$jikanModels = json_decode(
                file_get_contents(base_path("storage/app") . "/jikan_model_classes.json")
            );
        }

        return self::$jikanModels;
    }

    public function beginDatabaseTransaction(): void
    {
        $database = env('DB_DATABASE', 'jikan_tests');
        $testDatabase = $database === 'jikan' ? 'jikan_tests' : $database;

        $connection = DB::connection('mongodb');
        $resolvedDatabase = $connection->getMongoDB()->getDatabaseName();

        /*
         * Safety net: only ever truncate the isolated test database.
         *
         * Laravel's setUpTheTestEnvironment() calls our overridden setUpTraits()
         * (dynamic dispatch) during parent::setUp() — BEFORE this TestCase's own
         * setUp() has swapped the mongodb connection to the test DB. So
         * beginDatabaseTransaction() can run against the production database
         * (e.g. "jikan") first, truncating real data. Any mismatch now skips
         * the truncation instead.
         */
        if ($resolvedDatabase !== $testDatabase) {
            return;
        }

        $jikanModels = self::getJikanModels();
        $tablesWithoutModels = [
            "anime_characters_staff",
            "anime_episodes",
            "anime_episode",
            "anime_forum",
            "anime_moreinfo",
            "anime_news",
            "anime_pictures",
            "anime_recommendations",
            "anime_reviews",
            "anime_stats",
            "anime_userupdates",
            "anime_videos",
            "character_pictures",
            "characters_pictures",
            "clubs_members",
            "demographics_manga",
            "demographics_anime",
            "manga_characters",
            "manga_moreinfo",
            "manga_news",
            "manga_pictures",
            "manga_recommendations",
            "manga_reviews",
            "manga_stats",
            "manga_userupdates",
            "people_pictures",
            "reviews",
            "recommendations",
            "themes_anime",
            "themes_manga",
            "users_history",
            "users_friends",
            "users_recommendations",
            "users_reviews",
            "users_clubs"
        ];

        foreach ($jikanModels as $jikanModel)
        {
            if ($jikanModel === "App\\JikanApiSearchableModel") {
                continue;
            }
            if ($jikanModel::count() > 0)
            {
                $jikanModel::truncate();
            }
        }

        foreach ($tablesWithoutModels as $tableName)
        {
            DB::table($tableName)->truncate();
        }

        // The test DB is never migrated, but keyword search uses Mongo $text queries
        // (locally SCOUT_DRIVER is null). Recreate the text indexes the migration defines.
        self::ensureSearchIndexes();
    }

    /**
     * Recreate the text indexes defined in database/migrations/..._squash.php.
     * Truncate() on a Mongo collection drops it (and its indexes), so this must
     * run after each truncation for $text search to keep working.
     */
    private static function ensureSearchIndexes(): void
    {
        $definitions = [
            'anime' => [
                'fields' => ['title' => 'text', 'title_japanese' => 'text'],
                'weights' => ['title' => 50, 'title_japanese' => 5],
            ],
            'manga' => [
                'fields' => ['title' => 'text', 'title_japanese' => 'text'],
                'weights' => ['title' => 50, 'title_japanese' => 5],
            ],
            'characters' => [
                'fields' => ['name' => 'text', 'name_kanji' => 'text'],
                'weights' => ['name' => 50, 'name_kanji' => 5],
            ],
            'people' => [
                'fields' => ['name' => 'text', 'given_name' => 'text', 'family_name' => 'text'],
                'weights' => ['name' => 50, 'given_name' => 5, 'family_name' => 5],
            ],
            'genres_anime' => ['fields' => ['name' => 'text']],
            'genres_manga' => ['fields' => ['name' => 'text']],
            'explicit_genres_anime' => ['fields' => ['name' => 'text']],
            'explicit_genres_manga' => ['fields' => ['name' => 'text']],
            'themes_anime' => ['fields' => ['name' => 'text']],
            'themes_manga' => ['fields' => ['name' => 'text']],
            'demographics_anime' => ['fields' => ['name' => 'text']],
            'demographics_manga' => ['fields' => ['name' => 'text']],
            'clubs' => ['fields' => ['name' => 'text']],
            'magazines' => ['fields' => ['name' => 'text']],
            'producers' => ['fields' => ['name' => 'text']],
            'users' => ['fields' => ['internal_username' => 'text']],
        ];

        $mongodb = DB::connection('mongodb')->getMongoDB();

        foreach ($definitions as $collection => $def) {
            $options = ['name' => 'search'];
            if (isset($def['weights'])) {
                $options['weights'] = $def['weights'];
            }

            try {
                $mongoCollection = $mongodb->selectCollection($collection);

                // Make the definition authoritative: truncate() does not drop the
                // collection (and its indexes) here, and createIndex() with an
                // existing 'search' index simply conflicts and is swallowed — so a
                // stale/older index definition would silently survive. Drop it first.
                try {
                    $mongoCollection->dropIndex('search');
                } catch (\Throwable $e) {
                    // no 'search' index yet — nothing to drop
                }

                $mongoCollection->createIndex($def['fields'], $options);
            } catch (\Throwable $e) {
                // index may already exist / collection issues - not fatal for tests
            }
        }
    }
}
