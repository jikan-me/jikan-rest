<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->get('/', function () use ($router) {


    return response()->json([
    	'Author' => '@irfanDahir',
    	'Contact' => 'irfan@jikan.moe',
    	'JikanREST' => '3.0',
    	'JikanPHP' => '2.0.0-rc.1',
    	'Home' => 'https://jikan.moe',
    	'Docs' => 'https://jikan.docs.apiary.io',
    	'GitHub' => 'https://github.com/jikan-me/jikan',
    	'PRODUCTION_API_URL' => 'https://api.jikan.moe',
    	'STATUS_URL' => 'https://status.jikan.moe',
//    	'CACHED_REQUESTS' => app('redis')->dbSize(),
    ]);
});

$router->get('meta/{request:[A-Za-z]+}[/{type:[A-Za-z]+}[/{period:[A-Za-z]+}[/{page:[0-9]+}]]]', [
	'uses' => 'MetaLiteController@request'
]);

$router->group(['middleware' => []], function() use ($router) {

    $router->get('anime[/{id:[0-9]+}[/{extend:[A-Za-z_]+}[/{extendArgs}]]]', [
        'uses' => 'AnimeController@a'
    ]);

    $router->get('manga[/{id:[0-9]+}[/{extend:[A-Za-z]+}]]', [
        'uses' => 'MangaController@request'
    ]);

    $router->get('person[/{id:[0-9]+}[/{extend:[A-Za-z]+}]]', [
        'uses' => 'PersonController@request'
    ]);

    $router->get('character[/{id:[0-9]+}[/{extend:[A-Za-z]+}]]', [
        'uses' => 'CharacterController@request'
    ]);

    $router->get('search[/{type}[/{query}[/{page:[0-9]+}]]]', [
        'uses' => 'SearchController@request'
    ]);

    $router->get('season[/{year:[0-9]{4}}/{season:[A-Za-z]+}]', [
        'uses' => 'SeasonController@request'
    ]);

    $router->get('schedule[/{day:[A-Za-z]+}]', [
        'uses' => 'ScheduleController@request'
    ]);

    $router->get('top/{type:[A-Za-z]+}[/{page:[0-9]+}[/{subtype:[A-Za-z]+}]]', [
        'uses' => 'TopController@request'
    ]);
});


/*
 * REST v3
 */
$router->group(
    [
        'middleware' => [],
        'prefix' => 'v3'
    ],
    function() use ($router) {

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



    }
);


