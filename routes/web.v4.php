<?php


$router->get('/', function () use ($router) {
    return response()->json([
        'author_url' => 'http://irfan.dahir.co',
        'discord_url' => 'https://discord.gg/4tvCr36',
        'version' => '4.0',
        'parser_version' => JIKAN_PARSER_VERSION,
        'website_url' => 'https://jikan.moe',
        'documentation_url' => 'https://jikan.docs.apiary.io',
        'github_url' => 'https://github.com/jikan-me/jikan-me',
        'parser_github_url' => 'https://github.com/jikan-me/jikan',
        'production_api_url' => 'https://api.jikan.moe/v4/',
        'status_url' => 'https://status.jikan.moe'
    ]);
});

$router->group(
    [
        'prefix' => 'meta'
    ],
    function () use ($router) {
        $router->get('/status', [
           'uses' => 'MetaController@status'
        ]);

        $router->group(
            [
                'prefix' => 'requests'
            ],
            function () use ($router) {
                $router->get('/{type:[a-z]+}/{period:[a-z]+}[/{offset:[0-9]+}]', [
                   'uses' => 'MetaController@requests'
                ]);
            }
        );
    }
);

$router->group(
    [
        'prefix' => 'anime/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'AnimeController@main'
        ]);

        $router->get('/characters_staff', [
            'uses' => 'AnimeController@characters_staff'
        ]);

        $router->get('/episodes[/{page:[0-9]+}]', [
            'uses' => 'AnimeController@episodes'
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

        $router->get('/stats', [
            'uses' => 'AnimeController@stats'
        ]);

        $router->get('/moreinfo', [
            'uses' => 'AnimeController@moreInfo'
        ]);

        $router->get('/recommendations', [
            'uses' => 'AnimeController@recommendations'
        ]);

        $router->get('/userupdates[/{page:[0-9]+}]', [
            'uses' => 'AnimeController@userupdates'
        ]);

        $router->get('/reviews[/{page:[0-9]+}]', [
            'uses' => 'AnimeController@reviews'
        ]);
    }
);

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

        $router->get('/stats', [
            'uses' => 'MangaController@stats'
        ]);

        $router->get('/moreinfo', [
            'uses' => 'MangaController@moreInfo'
        ]);

        $router->get('/recommendations', [
            'uses' => 'MangaController@recommendations'
        ]);

        $router->get('/userupdates[/{page:[0-9]+}]', [
            'uses' => 'MangaController@userupdates'
        ]);

        $router->get('/reviews[/{page:[0-9]+}]', [
            'uses' => 'MangaController@reviews'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'character/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'CharacterController@main'
        ]);

        $router->get('/pictures', [
            'uses' => 'CharacterController@pictures'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'person/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'PersonController@main'
        ]);

        $router->get('/pictures', [
            'uses' => 'PersonController@pictures'
        ]);
    }
);

$router->get('season/archive', [
    'uses' => 'SeasonController@archive'
]);

$router->get('season/later', [
    'uses' => 'SeasonController@later'
]);

$router->get('season[/{year:[0-9]{4}}/{season:[A-Za-z]+}]', [
    'uses' => 'SeasonController@main'
]);

$router->get('schedule[/{day:[A-Za-z]+}]', [
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

        $router->get('/{id:[0-9]+}[/{page:[0-9]+}]', [
            'uses' => 'ProducerController@resource'
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

        $router->get('/{id:[0-9]+}[/{page:[0-9]+}]', [
            'uses' => 'MagazineController@resource'
        ]);
    }
);


$router->group(
    [
        'prefix' => 'users/{username:[\w\-]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'UserController@profile'
        ]);

        $router->get('/history[/{type:[A-Za-z]+}]', [
            'uses' => 'UserController@history'
        ]);

        $router->get('/friends[/{page:[0-9]+}]', [
            'uses' => 'UserController@friends'
        ]);

        $router->get('/animelist[/{status:[A-Za-z]+}[/{page:[0-9]+}]]', [
            'uses' => 'UserController@animelist'
        ]);

        $router->get('/mangalist[/{status:[A-Za-z]+}[/{page:[0-9]+}]]', [
            'uses' => 'UserController@mangalist'
        ]);

        $router->get('/recommendations[/{page:[0-9]+}]', [
            'uses' => 'UserController@recommendations'
        ]);

        $router->get('/reviews[/{page:[0-9]+}]', [
            'uses' => 'UserController@reviews'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'genres'
    ],
    function () use ($router) {
        $router->get('/anime', [
            'uses' => 'GenreController@animeListing'
        ]);

        $router->get('/manga', [
            'uses' => 'GenreController@mangaListing'
        ]);

        $router->get('/anime/{id:[0-9]+}[/{page:[0-9]+}]', [
            'uses' => 'GenreController@anime'
        ]);

        $router->get('/manga/{id:[0-9]+}[/{page:[0-9]+}]', [
            'uses' => 'GenreController@manga'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'top'
    ],
    function () use ($router) {
        $router->get('/anime[/{page:[0-9]+}[/{type:[A-Za-z]+}]]', [
            'uses' => 'TopController@anime'
        ]);

        $router->get('/manga[/{page:[0-9]+}[/{type:[A-Za-z]+}]]', [
            'uses' => 'TopController@manga'
        ]);

        $router->get('/characters[/{page:[0-9]+}]', [
            'uses' => 'TopController@characters'
        ]);

        $router->get('/people[/{page:[0-9]+}]', [
            'uses' => 'TopController@people'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'search'
    ],
    function () use ($router) {
        $router->get('/anime[/{page:[0-9]+}]', [
            'uses' => 'SearchController@anime'
        ]);

        $router->get('/manga[/{page:[0-9]+}]', [
            'uses' => 'SearchController@manga'
        ]);

        $router->get('/character[/{page:[0-9]+}]', [
            'uses' => 'SearchController@character'
        ]);

        $router->get('/person[/{page:[0-9]+}]', [
            'uses' => 'SearchController@people'
        ]);

        $router->get('/people[/{page:[0-9]+}]', [
            'uses' => 'SearchController@people'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'club/{id:[0-9]+}'
    ],
    function () use ($router) {
        $router->get('/', [
            'uses' => 'ClubController@main'
        ]);

        $router->get('/members[/{page:[0-9]+}]', [
            'uses' => 'ClubController@members'
        ]);
    }
);
