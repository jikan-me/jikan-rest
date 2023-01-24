<?php

return [
    'default_cache_expire' => env('CACHE_DEFAULT_EXPIRE', 86400),
    'per_endpoint_cache_ttl' => [
        /**
         * Anime
         */
        'AnimeController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'AnimeController@characters_staff' => env('CACHE_DEFAULT_EXPIRE'),
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

        /**
         * Manga
         */
        'MangaController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@characters' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@news' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@forum' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@pictures' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@stats' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@moreInfo' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@recommendations' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@userupdates' => env('CACHE_DEFAULT_EXPIRE'),
        'MangaController@reviews' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Characters
         */
        'CharacterController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@voices' => env('CACHE_DEFAULT_EXPIRE'),
        'CharacterController@pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Person
         */
        'PersonController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@manga' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@seiyuu' => env('CACHE_DEFAULT_EXPIRE'),
        'PersonController@pictures' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Season
         */
        'SeasonController@archive' => env('CACHE_DEFAULT_EXPIRE'),
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

        /**
         * Magazines
         */
        'MagazineController@main' => env('CACHE_MAGAZINE_EXPIRE'),
        'MagazineController@resource' => env('CACHE_DEFAULT_EXPIRE'),

        /**
         * Users
         */
        'UserController@recentlyOnline' => env('CACHE_DEFAULT_EXPIRE'),
        'UserController@profile' => env('CACHE_USER_EXPIRE'),
        'UserController@statistics' => env('CACHE_USER_EXPIRE'),
        'UserController@favorites' => env('CACHE_USER_EXPIRE'),
        'UserController@about' => env('CACHE_USER_EXPIRE'),
        'UserController@history' => env('CACHE_USER_EXPIRE'),
        'UserController@friends' => env('CACHE_USER_EXPIRE'),
        'UserController@recommendations' => env('CACHE_USER_EXPIRE'),
        'UserController@reviews' => env('CACHE_USER_EXPIRE'),
        'UserController@clubs' => env('CACHE_USER_EXPIRE'),

        /**
         * User Lists
         */
        'UserController@animelist' => env('CACHE_USERLIST_EXPIRE'),
        'UserController@mangalist' => env('CACHE_USERLIST_EXPIRE'),

        /**
         * Genre
         */
        'GenreController@mainAnime' => env('CACHE_GENRE_EXPIRE'),
        'GenreController@mainManga' => env('CACHE_GENRE_EXPIRE'),
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

        'ClubController@main' => env('CACHE_DEFAULT_EXPIRE'),
        'ClubController@members' => env('CACHE_DEFAULT_EXPIRE'),

        'ReviewsController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'ReviewsController@manga' => env('CACHE_DEFAULT_EXPIRE'),

        'RecommendationsController@anime' => env('CACHE_DEFAULT_EXPIRE'),
        'RecommendationsController@manga' => env('CACHE_DEFAULT_EXPIRE'),

        'WatchController@recentEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@popularEpisodes' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@recentPromos' => env('CACHE_DEFAULT_EXPIRE'),
        'WatchController@popularPromos' => env('CACHE_DEFAULT_EXPIRE'),
    ]
];
