<?php

return [
    'max_results_per_page' => env('MAX_RESULTS_PER_PAGE', 25),
    'micro_caching_enabled' => env('MICROCACHING', false),
    'default_cache_expire' => env('CACHE_DEFAULT_EXPIRE', 86400),
    // ------------------------------------------------------------------------
    // Application settings. Centralised here (formerly read via env() inside
    // app code), which keeps the app safe under `php artisan config:cache`.
    // Defaults mirror .env.dist.
    // ------------------------------------------------------------------------
    'source_timeout' => (int) env('SOURCE_TIMEOUT', 10),
    'queue_delay_per_job' => (int) env('QUEUE_DELAY_PER_JOB', 5),
    'github_reporting' => (bool) env('GITHUB_REPORTING', true),
    'github_rest' => env('GITHUB_REST', 'jikan-me/jikan-rest'),
    'github_api' => env('GITHUB_API', 'jikan-me/jikan'),
    'disable_user_lists' => env('DISABLE_USER_LISTS', false),
    // Dedicated token for the internal cache-bypass (jobs refreshing cached
    // responses). Empty by default — no bypass. Was previously env('APP_KEY').
    'cache_bypass_token' => env('CACHE_BYPASS_TOKEN', ''),
    'microcaching_expire' => (int) env('MICROCACHING_EXPIRE', 5),
    'rate_limit_per_minute' => (int) env('RATE_LIMIT_PER_MINUTE', 240),
    // Source-heartbeat (MAL availability) scoring.
    'source_good_health_score' => (float) env('SOURCE_GOOD_HEALTH_SCORE', 0.9),
    'source_bad_health_recheck' => (int) env('SOURCE_BAD_HEALTH_RECHECK', 10),
    'source_bad_health_range' => (int) env('SOURCE_BAD_HEALTH_RANGE', 30),
    'source_bad_health_max_store' => (int) env('SOURCE_BAD_HEALTH_MAX_STORE', 50),
    'typesense_options' => [
        'text_match_buckets' => env('TYPESENSE_TEXT_MATCH_BUCKETS', 1),
        'typo_tokens_threshold' => env('TYPESENSE_TYPO_TOKENS_THRESHOLD'),
        'drop_tokens_threshold' => env('TYPESENSE_DROP_TOKENS_THRESHOLD'),
        'search_cutoff_ms' => env('TYPESENSE_SEARCH_CUTOFF_MS', 450),
        'exhaustive_search' => env('TYPESENSE_ENABLE_EXHAUSTIVE_SEARCH', 'false')
    ],
    'per_endpoint_cache_ttl' => [
        /**
         * Key format: "Controller@action", matching HttpHelper::getRouteName()
         * (last "\\" segment of the route's "uses"). Re-synced against the
         * registered v4 routes; Tests\Unit\CacheTtlMapTest guards this map so a
         * new route action can't silently fall back to CACHE_DEFAULT_EXPIRE.
         *
         * Anime
         */
        'AnimeController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@full' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@characters' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@staff' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@episodes' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@episode' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@news' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@forum' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@videos' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@videosEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@pictures' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@stats' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@moreInfo' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@recommendations' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@userupdates' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@reviews' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@relations' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@themes' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@external' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@streaming' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Manga
         */
        'MangaController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@full' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@characters' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@news' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@forum' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@pictures' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@stats' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@moreInfo' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@recommendations' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@userupdates' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@reviews' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@relations' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@external' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Characters
         */
        'CharacterController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@full' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@voices' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Person
         */
        'PersonController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@full' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@voices' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Season
         */
        'SeasonController@archive' => env('CACHE_DEFAULT_EXPIRE'),
        'SeasonController@now' => env('CACHE_DEFAULT_EXPIRE'),
        'SeasonController@later' => env('CACHE_DEFAULT_EXPIRE'),
        'SeasonController@main' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Schedule
         */
        'ScheduleController@main' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Producers
         */
        'ProducerController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'ProducerController@full' => env('CACHE_DEFAULT_EXPIRE'),
        'ProducerController@external' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Magazines
         */
        'MagazineController@main' => env('CACHE_MAGAZINE_EXPIRE'),

        /**
         * Users
         */
        'UserController@recentlyOnline' => env('CACHE_DEFAULT_EXPIRE'),
        'UserController@profile' => env('CACHE_USER_EXPIRE'),
        'UserController@full' => env('CACHE_USER_EXPIRE'),
        'UserController@statistics' => env('CACHE_USER_EXPIRE'),
        'UserController@favorites' => env('CACHE_USER_EXPIRE'),
        'UserController@userupdates' => env('CACHE_USER_EXPIRE'),
        'UserController@about' => env('CACHE_USER_EXPIRE'),
        'UserController@history' => env('CACHE_USER_EXPIRE'),
        'UserController@friends' => env('CACHE_USER_EXPIRE'),
        'UserController@recommendations' => env('CACHE_USER_EXPIRE'),
        'UserController@reviews' => env('CACHE_USER_EXPIRE'),
        'UserController@clubs' => env('CACHE_USER_EXPIRE'),
        'UserController@external' => env('CACHE_USER_EXPIRE'),

        /**
         * User Lists
         */
        'UserController@animelist' => env('CACHE_USERLIST_EXPIRE'),
        'UserController@mangalist' => env('CACHE_USERLIST_EXPIRE'),

        /**
         * Genre
         */
        'GenreController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'GenreController@manga' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Top
         */
        'TopController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'TopController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'TopController@characters' => env('CACHE_DEFAULT_EXPIRE'),
        'TopController@people' => env('CACHE_DEFAULT_EXPIRE'),
        'TopController@reviews' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Search
         */
        'SearchController@anime' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@manga' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@character' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@people' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@users' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@userById' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@producers' => env('CACHE_SEARCH_EXPIRE'),
        'SearchController@clubs' => env('CACHE_SEARCH_EXPIRE'),

        'ClubController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'ClubController@members' => env('CACHE_DEFAULT_EXPIRE'),
        'ClubController@staff' => env('CACHE_DEFAULT_EXPIRE'),
        'ClubController@relations' => env('CACHE_DEFAULT_EXPIRE'),

        'ReviewsController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'ReviewsController@manga' => env('CACHE_DEFAULT_EXPIRE'),

        'RecommendationsController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'RecommendationsController@manga' => env('CACHE_DEFAULT_EXPIRE'),

        'WatchController@recentEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@popularEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@recentPromos' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@popularPromos' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Random
         */
        'RandomController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'RandomController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'RandomController@characters' => env('CACHE_DEFAULT_EXPIRE'),
        'RandomController@people' => env('CACHE_DEFAULT_EXPIRE'),
        'RandomController@users' => env('CACHE_DEFAULT_EXPIRE'),
    ]
];
