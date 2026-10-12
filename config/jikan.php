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
    // Opt-in error reporting. Sentry is only registered when REPORTING is true
    // and REPORTING_DRIVER is `sentry` (same contract as master).
    'reporting' => (bool) env('REPORTING', false),
    'reporting_driver' => env('REPORTING_DRIVER', 'sentry'),
    'github_rest' => env('GITHUB_REST', 'jikan-me/jikan-rest'),
    'github_api' => env('GITHUB_API', 'jikan-me/jikan'),
    'disable_user_lists' => env('DISABLE_USER_LISTS', false),
    'microcaching_expire' => (int) env('MICROCACHING_EXPIRE', 60),
    // Per-IP requests/minute for the v4 API. 0 (default) disables the limiter.
    'rate_limit_per_minute' => (int) env('RATE_LIMIT_PER_MINUTE', 0),
    // Source-heartbeat (MAL availability) scoring.
    'source_good_health_score' => (float) env('SOURCE_GOOD_HEALTH_SCORE', 0.9),
    'source_bad_health_recheck' => (int) env('SOURCE_BAD_HEALTH_RECHECK', 10),
    'source_heartbeat_interval' => (float) env('SOURCE_HEARTBEAT_INTERVAL', 1.0),
    'source_bad_health_range' => (int) env('SOURCE_BAD_HEALTH_RANGE', 30),
    'source_bad_health_max_store' => (int) env('SOURCE_BAD_HEALTH_MAX_STORE', 50),
    'typesense_options' => [
        'text_match_buckets' => env('TYPESENSE_TEXT_MATCH_BUCKETS', 1),
        'typo_tokens_threshold' => env('TYPESENSE_TYPO_TOKENS_THRESHOLD'),
        'drop_tokens_threshold' => env('TYPESENSE_DROP_TOKENS_THRESHOLD'),
        'search_cutoff_ms' => env('TYPESENSE_SEARCH_CUTOFF_MS', 450),
        'exhaustive_search' => env('TYPESENSE_ENABLE_EXHAUSTIVE_SEARCH', 'false'),
    ],
    'per_endpoint_cache_ttl' => [
        /**
         * Key format: the route name, "v4.<resource>.<action>" (see routes/web.v4.php),
         * which is what HttpHelper::getRouteName() resolves per request. CacheTtlMapTest
         * guards this map so a new route can't silently fall back to CACHE_DEFAULT_EXPIRE.
         *
         * Anime
         */
        'v4.anime.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.full' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.characters' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.staff' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.episodes' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.episode' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.news' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.forum' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.videos' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.videosEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.pictures' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.stats' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.moreInfo' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.recommendations' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.userupdates' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.reviews' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.relations' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.themes' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.external' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.anime.streaming' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Manga
         */
        'v4.manga.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.full' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.characters' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.news' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.forum' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.pictures' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.stats' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.moreInfo' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.recommendations' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.userupdates' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.reviews' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.relations' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.manga.external' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Characters
         */
        'v4.character.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.character.full' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.character.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.character.manga' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.character.voices' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.character.pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Person
         */
        'v4.person.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.person.full' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.person.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.person.manga' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.person.voices' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.person.pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Season
         */
        'v4.season.archive' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.season.now' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.season.later' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.season.main' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Schedule
         */
        'v4.schedule.main' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Producers
         */
        'v4.producer.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.producer.full' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.producer.external' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Magazines
         */
        'v4.magazine.main' => env('CACHE_MAGAZINE_EXPIRE'),

        /**
         * Users
         */
        'v4.user.recentlyOnline' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.user.profile' => env('CACHE_USER_EXPIRE'),
        'v4.user.full' => env('CACHE_USER_EXPIRE'),
        'v4.user.statistics' => env('CACHE_USER_EXPIRE'),
        'v4.user.favorites' => env('CACHE_USER_EXPIRE'),
        'v4.user.userupdates' => env('CACHE_USER_EXPIRE'),
        'v4.user.about' => env('CACHE_USER_EXPIRE'),
        'v4.user.history' => env('CACHE_USER_EXPIRE'),
        'v4.user.friends' => env('CACHE_USER_EXPIRE'),
        'v4.user.recommendations' => env('CACHE_USER_EXPIRE'),
        'v4.user.reviews' => env('CACHE_USER_EXPIRE'),
        'v4.user.clubs' => env('CACHE_USER_EXPIRE'),
        'v4.user.external' => env('CACHE_USER_EXPIRE'),

        /**
         * User Lists
         */
        'v4.user.animelist' => env('CACHE_USERLIST_EXPIRE'),
        'v4.user.mangalist' => env('CACHE_USERLIST_EXPIRE'),

        /**
         * Genre
         */
        'v4.genre.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.genre.manga' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Top
         */
        'v4.top.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.top.manga' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.top.characters' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.top.people' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.top.reviews' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Search
         */
        'v4.search.anime' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.manga' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.character' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.people' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.users' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.userById' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.producers' => env('CACHE_SEARCH_EXPIRE'),
        'v4.search.clubs' => env('CACHE_SEARCH_EXPIRE'),

        'v4.club.main' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.club.members' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.club.staff' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.club.relations' => env('CACHE_DEFAULT_EXPIRE'),

        'v4.reviews.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.reviews.manga' => env('CACHE_DEFAULT_EXPIRE'),

        'v4.recommendations.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.recommendations.manga' => env('CACHE_DEFAULT_EXPIRE'),

        'v4.watch.recentEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.watch.popularEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.watch.recentPromos' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.watch.popularPromos' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Random
         */
        'v4.random.anime' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.random.manga' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.random.characters' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.random.people' => env('CACHE_DEFAULT_EXPIRE'),
        'v4.random.users' => env('CACHE_DEFAULT_EXPIRE'),
    ],
];
