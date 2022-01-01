<?php


$router->get('/', function () use ($router) {
    return response()->json([
        'author_url' => 'https://github.com/irfan-dahir',
        'discord_url' => 'http://discord.jikan.moe',
        'version' => env('APP_VERSION'),
        'parser_version' => JIKAN_PARSER_VERSION,
        'website_url' => 'https://jikan.moe',
        'documentation_url' => 'https://docs.api.jikan.moe/',
        'github_url' => 'https://github.com/jikan-me/jikan-rest',
        'parser_github_url' => 'https://github.com/jikan-me/jikan',
        'production_api_url' => 'https://api.jikan.moe/v4/',
        'status_url' => 'https://status.jikan.moe',
        'myanimelist_heartbeat' => [
            'status' => \App\Providers\SourceHeartbeatProvider::getHeartbeatStatus(),
            'score' => \App\Providers\SourceHeartbeatProvider::getHeartbeatScore(),
            'down' => \App\Providers\SourceHeartbeatProvider::isFailoverEnabled(),
            'last_downtime' => \App\Providers\SourceHeartbeatProvider::getLastDowntime()
        ]
    ]);
});

$router->get('/anime', [
    'uses' => 'SearchController@anime'
]);

$router->group(
    [
        'prefix' => 'anime/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'AnimeController@main'
        ]);

        $router->get('/characters', [
            'uses' => 'AnimeController@characters'
        ]);

        $router->get('/staff', [
            'uses' => 'AnimeController@staff'
        ]);

        $router->get('/episodes', [
            'uses' => 'AnimeController@episodes'
        ]);

        $router->get('/episodes/{episodeId:[0-9]+}', [
            'uses' => 'AnimeController@episode'
        ]);

        $router->get('/news', [
            'uses' => 'AnimeController@news'
        ]);

        $router->get('/forum', [
            'uses' => 'AnimeController@forum'
        ]);

        $router->get('/videos', [
            'uses' => 'AnimeController@videos'
        ]);

        $router->get('/pictures', [
            'uses' => 'AnimeController@pictures'
        ]);

        $router->get('/statistics', [
            'uses' => 'AnimeController@stats'
        ]);

        $router->get('/moreinfo', [
            'uses' => 'AnimeController@moreInfo'
        ]);

        $router->get('/recommendations', [
            'uses' => 'AnimeController@recommendations'
        ]);

        $router->get('/userupdates', [
            'uses' => 'AnimeController@userupdates'
        ]);

        $router->get('/reviews', [
            'uses' => 'AnimeController@reviews'
        ]);

        $router->get('/relations', [
            'uses' => 'AnimeController@relations'
        ]);

        $router->get('/themes', [
            'uses' => 'AnimeController@themes'
        ]);
    }
);

$router->get('/manga', [
    'uses' => 'SearchController@manga'
]);

$router->group(
    [
        'prefix' => 'manga/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'MangaController@main'
        ]);

        $router->get('/characters', [
            'uses' => 'MangaController@characters'
        ]);

        $router->get('/news', [
            'uses' => 'MangaController@news'
        ]);

        $router->get('/forum', [
            'uses' => 'MangaController@forum'
        ]);

        $router->get('/pictures', [
            'uses' => 'MangaController@pictures'
        ]);

        $router->get('/statistics', [
            'uses' => 'MangaController@stats'
        ]);

        $router->get('/moreinfo', [
            'uses' => 'MangaController@moreInfo'
        ]);

        $router->get('/recommendations', [
            'uses' => 'MangaController@recommendations'
        ]);

        $router->get('/userupdates', [
            'uses' => 'MangaController@userupdates'
        ]);

        $router->get('/reviews', [
            'uses' => 'MangaController@reviews'
        ]);

        $router->get('/relations', [
            'uses' => 'MangaController@relations'
        ]);
    }
);

$router->get('/characters', [
    'uses' => 'SearchController@character'
]);

$router->group(
    [
        'prefix' => 'characters/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'CharacterController@main'
        ]);

        $router->get('/anime', [
            'uses' => 'CharacterController@anime'
        ]);

        $router->get('/voices', [
            'uses' => 'CharacterController@voices'
        ]);

        $router->get('/manga', [
            'uses' => 'CharacterController@manga'
        ]);

        $router->get('/pictures', [
            'uses' => 'CharacterController@pictures'
        ]);
    }
);

$router->get('/people', [
    'uses' => 'SearchController@people'
]);
$router->group(
    [
        'prefix' => 'people/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'PersonController@main'
        ]);

        $router->get('/anime', [
            'uses' => 'PersonController@anime'
        ]);

        $router->get('/voices', [
            'uses' => 'PersonController@voices'
        ]);

        $router->get('/manga', [
            'uses' => 'PersonController@manga'
        ]);

        $router->get('/pictures', [
            'uses' => 'PersonController@pictures'
        ]);
    }
);


$router->group(
    [
        'prefix' => 'seasons'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'SeasonController@archive'
        ]);

        $router->get('/now', [
            'uses' => 'SeasonController@main'
        ]);

        $router->get('/upcoming', [
            'uses' => 'SeasonController@later'
        ]);

        $router->get('/{year:[0-9]{4}}/{season:[A-Za-z]+}', [
            'uses' => 'SeasonController@main'
        ]);
    }
);

$router->get('schedules[/{day:[A-Za-z]+}]', [
    'uses' => 'ScheduleController@main'
]);

$router->group(
    [
        'prefix' => 'producers'
    ],
    function() use ($router) {
        $router->get('/', [
            'uses' => 'ProducerController@main',
        ]);
    }
);

$router->group(
    [
        'prefix' => 'magazines'
    ],
    function() use ($router) {
        $router->get('/', [
            'uses' => 'MagazineController@main',
        ]);
    }
);


$router->group(
    [
        'prefix' => 'users'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'SearchController@users'
        ]);

        $router->get('/recentlyonline', [
            'uses' => 'UserController@recentlyOnline'
        ]);

        $router->get('/userbyid/{id:[0-9]+}', [
            'uses' => 'SearchController@userById'
        ]);

        $router->group(
            [
                'prefix' => '/{username:[\w\-]+}'
            ],
            function () use ($router) {
                $router->get('/', [
                    'uses' => 'UserController@profile'
                ]);

                $router->get('/statistics', [
                    'uses' => 'UserController@statistics'
                ]);

                $router->get('/favorites', [
                    'uses' => 'UserController@favorites'
                ]);

                $router->get('/userupdates', [
                    'uses' => 'UserController@userupdates'
                ]);

                $router->get('/about', [
                    'uses' => 'UserController@about'
                ]);

                $router->get('/history[/{type:[A-Za-z]+}]', [
                    'uses' => 'UserController@history'
                ]);

                $router->get('/friends[/{page:[0-9]+}]', [
                    'uses' => 'UserController@friends'
                ]);

                $router->get('/animelist[/{status:[A-Za-z]+}]', [
                    'uses' => 'UserController@animelist'
                ]);

                $router->get('/mangalist[/{status:[A-Za-z]+}]', [
                    'uses' => 'UserController@mangalist'
                ]);

                $router->get('/recommendations', [
                    'uses' => 'UserController@recommendations'
                ]);

                $router->get('/reviews', [
                    'uses' => 'UserController@reviews'
                ]);

                $router->get('/clubs', [
                    'uses' => 'UserController@clubs'
                ]);
            }
        );
    }
);

$router->group(
    [
        'prefix' => 'genres'
    ],
    function () use ($router) {

        $router->get('/anime', [
            'uses' => 'GenreController@anime'
        ]);

        $router->get('/manga', [
            'uses' => 'GenreController@manga'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'top'
    ],
    function () use ($router) {
        $router->get('/anime', [
            'uses' => 'TopController@anime'
        ]);

        $router->get('/manga', [
            'uses' => 'TopController@manga'
        ]);

        $router->get('/characters', [
            'uses' => 'TopController@characters'
        ]);

        $router->get('/people', [
            'uses' => 'TopController@people'
        ]);

        $router->get('/reviews', [
            'uses' => 'TopController@reviews'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'clubs'
    ],
    function () use ($router) {

        $router->get('/{id:[0-9]+}', [
            'uses' => 'ClubController@main'
        ]);

        $router->get('/{id:[0-9]+}/members', [
            'uses' => 'ClubController@members'
        ]);

        $router->get('/{id:[0-9]+}/staff', [
            'uses' => 'ClubController@staff'
        ]);

        $router->get('/{id:[0-9]+}/relations', [
            'uses' => 'ClubController@relations'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'reviews'
    ],
    function () use ($router) {

        $router->get('/anime', [
            'uses' => 'ReviewsController@anime'
        ]);

        $router->get('/manga', [
            'uses' => 'ReviewsController@manga'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'recommendations'
    ],
    function () use ($router) {
        $router->get('/anime', [
            'uses' => 'RecommendationsController@anime'
        ]);

        $router->get('/manga', [
            'uses' => 'RecommendationsController@manga'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'watch'
    ],
    function () use ($router) {
        $router->get('/episodes', [
            'uses' => 'WatchController@recentEpisodes'
        ]);

        $router->get('/episodes/popular', [
            'uses' => 'WatchController@popularEpisodes'
        ]);

        $router->get('/promos', [
            'uses' => 'WatchController@recentPromos'
        ]);

        $router->get('/promos/popular', [
            'uses' => 'WatchController@popularPromos'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'random'
    ],
    function() use ($router) {
        $router->get('/anime', [
            'uses' => 'RandomController@anime',
        ]);

        $router->get('/manga', [
            'uses' => 'RandomController@manga',
        ]);

        $router->get('/characters', [
            'uses' => 'RandomController@characters',
        ]);

        $router->get('/people', [
            'uses' => 'RandomController@people',
        ]);

        $router->get('/users', [
            'uses' => 'RandomController@users',
        ]);
    }
);


$router->group(
    [
        'prefix' => 'insights'
    ],
    function() use ($router) {
        $router->get('/', [
            'uses' => 'InsightsController@main'
        ]);

        $router->get('/trends', [
            'uses' => 'InsightsController@trends'
        ]);
    }
);