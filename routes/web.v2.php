<?php


$router->get('/', function () use ($router) {


    return response()->json([
    	'Author' => '@irfanDahir',
    	'Contact' => 'irfan@jikan.moe',
    	'JikanREST' => '2.0',
    	'JikanPHP' => '2.0.0-rc.1',
    	'Website' => 'https://jikan.moe',
    	'Docs' => 'https://jikan.docs.apiary.io',
    	'GitHub' => 'https://github.com/jikan-me/jikan',
    	'PRODUCTION_API_URL' => 'https://api.jikan.moe/v2/',
    	'STATUS_URL' => 'https://status.jikan.moe',
        'NOTICE' => 'Switch over to V3 before the End Of Life for V2! (January 1st, 2019)'
//    	'CACHED_REQUESTS' => app('redis')->dbSize(),
    ]);
});


$router->group(
    [
        'prefix' => 'anime/{id:[0-9]+}'
    ],
    function() use ($router) {
        $router->get('/', [
            'uses' => 'AnimeController@main'
        ]);

        $router->get('/characters_staff', [
            'uses' => 'AnimeController@characters_staff'
        ]);

        $router->get('/episodes/{page:[0-9]+}', [
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
    }
);

$router->group(
    [
        'prefix' => 'manga/{id:[0-9]+}'
    ],
    function() use ($router) {
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
    }
);

$router->group(
    [
        'prefix' => 'character/{id:[0-9]+}'
    ],
    function() use ($router) {
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
    function() use ($router) {
        $router->get('/', [
            'uses' => 'PersonController@main'
        ]);

        $router->get('/pictures', [
            'uses' => 'PersonController@pictures'
        ]);
    }
);

$router->get('season[/{year:[0-9]{4}}/{season:[A-Za-z]+}]', [
    'uses' => 'SeasonController@main'
]);

$router->get('schedule[/{day:[A-Za-z]+}]', [
    'uses' => 'ScheduleController@main'
]);

$router->get('producer/{id:[0-9]+}[/{page:[0-9]+}]', [
    'uses' => 'ProducerController@main'
]);

$router->get('magazine/{id:[0-9]+}[/{page:[0-9]+}]', [
    'uses' => 'MagazineController@main'
]);

$router->group(
    [
        'prefix' => 'user/{username:[\w\-]+}'
    ],
    function() use ($router) {
        $router->get('/', [
            'uses' => 'UserController@profile'
        ]);

        $router->get('/history[/{type:[A-Za-z]+}]', [
            'uses' => 'UserController@history'
        ]);

        $router->get('/friends[/{page:[0-9]+}]', [
            'uses' => 'UserController@friends'
        ]);
    }
);

$router->group(
    [
        'prefix' => 'genre'
    ],
    function() use ($router) {
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
    function() use ($router) {

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
    function() use ($router) {

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
            'uses' => 'SearchController@person'
        ]);

        $router->get('/people[/{page:[0-9]+}]', [
            'uses' => 'SearchController@person'
        ]);

    }
);
