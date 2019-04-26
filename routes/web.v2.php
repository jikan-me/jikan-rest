<?php

$router->get('/', function () use ($router) {


    return response()->json([
        'DEPRECIATION_NOTICE' => 'THIS VERSION WILL BE DEPRECIATED ON July 01, 2019.',
    	'Author' => '@irfanDahir',
        'Discord' => 'https://discord.gg/4tvCr36',
        'Version' => '2.0',
        'JikanPHP' => '2.7.0',
        'Website' => 'https://jikan.moe',
    	'Docs' => 'https://jikan.docs.apiary.io',
    	'GitHub' => 'https://github.com/jikan-me/jikan',
    	'PRODUCTION_API_URL' => 'https://api.jikan.moe/v2/',
    	'STATUS_URL' => 'https://status.jikan.moe'
    ]);
});

$router->group(
    [
        'prefix' => 'meta'
    ],
    function() use ($router) {
        $router->get('/status', [
            'uses' => 'MetaController@status'
        ]);

        $router->group(
            [
                'prefix' => 'requests'
            ],
            function() use ($router) {
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
    function() use ($router) {
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
    }
);

$router->group(
    [
        'prefix' => 'search'
    ],
    function() use ($router) {
        $router->get('/anime[/{query}[/{page:[0-9]+}]]', [
            'uses' => 'SearchController@anime'
        ]);

        $router->get('/manga[/{query}[/{page:[0-9]+}]]', [
            'uses' => 'SearchController@manga'
        ]);

        $router->get('/character[/{query}[/{page:[0-9]+}]]', [
            'uses' => 'SearchController@character'
        ]);

        $router->get('/person[/{query}[/{page:[0-9]+}]]', [
            'uses' => 'SearchController@people'
        ]);

        $router->get('/people[/{query}[/{page:[0-9]+}]]', [
            'uses' => 'SearchController@people'
        ]);

    }
);
