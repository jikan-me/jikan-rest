<?php

use PackageVersions\Versions;

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
    Defines
*/
defined('JIKAN_PARSER_VERSION') or define('JIKAN_PARSER_VERSION', Versions::getVersion('jikan-me/jikan'));
defined('JIKAN_REST_API_VERSION') or define('JIKAN_REST_API_VERSION', '3.4.3');


/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/


$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$app->withFacades();
$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);


/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->routeMiddleware([
    'meta' => App\Http\Middleware\Meta::class,
    'jikan-response' => App\Http\Middleware\JikanResponseHandler::class,
    'throttle' => App\Http\Middleware\Throttle::class,
    'etag' => \App\Http\Middleware\EtagMiddleware::class,
    'microcaching' => \App\Http\Middleware\MicroCaching::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->configure('database');
$app->configure('queue');
$app->configure('cache');

$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);

$guzzleClient = new \GuzzleHttp\Client();
$app->instance('GuzzleClient', $guzzleClient);

$jikan = new \Jikan\MyAnimeList\MalClient(app('GuzzleClient'));
$app->instance('JikanParser', $jikan);


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$commonMiddleware = [
    'meta',
    'etag',
    'microcaching',
    'jikan-response',
    'throttle'
];

/*$app->router->group(
    [
        'prefix' => 'v4',
        'namespace' => 'App\Http\Controllers\V4',
        'middleware' => $commonMiddleware
    ],
    function ($router) {
        require __DIR__.'/../routes/web.v4.php';
    }
);*/

$app->router->group(
    [
        'prefix' => 'v3',
        'namespace' => 'App\Http\Controllers\V3',
        'middleware' => $commonMiddleware
    ],
    function ($router) {
        require __DIR__.'/../routes/web.v3.php';
    }
);

$app->router->group(
    [
        'prefix' => '/',
        'namespace' => 'App\Http\Controllers\V3',
        'middleware' => $commonMiddleware
    ],
    function ($router) {
        $router->get('/', function () {
            return response()->json([
                'NOTICE' => 'Append an API version for API requests. Please check the documentation for the latest and supported versions.',
                'Author' => '@irfanDahir',
                'Discord' => 'http://discord.jikan.moe',
                'Version' => JIKAN_REST_API_VERSION,
                'JikanPHP' => JIKAN_PARSER_VERSION,
                'Website' => 'https://jikan.moe',
                'Docs' => 'https://jikan.docs.apiary.io',
                'GitHub' => 'https://github.com/jikan-me/jikan',
                'PRODUCTION_API_URL' => 'https://api.jikan.moe/v3/',
                'STATUS_URL' => 'https://status.jikan.moe'
            ]);
        });
    }
);

$app->router->group(
    [
        'prefix' => 'v1',
    ],
    function ($router) {
        $router->get('/', function () {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'HttpException',
                    'message' => 'This version is depreciated. Please check the documentation for the latest and supported versions.',
                    'error' => null
                ], 400);
        });
    }
);

$app->router->group(
    [
        'prefix' => 'v2',
    ],
    function ($router) {
        $router->get('/', function () {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'HttpException',
                    'message' => 'This version is depreciated. Please check the documentation for the latest and supported versions.',
                    'error' => null
                ], 400);
        });
    }
);


return $app;
