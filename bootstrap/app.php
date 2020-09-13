<?php

use App\Providers\SerializerFactory;
use PackageVersions\Versions;

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
    Defines
*/
defined('BLACKLIST_PATH') or define('BLACKLIST_PATH', __DIR__.'/../storage/app/blacklist.json');
defined('JIKAN_PARSER_VERSION') or define('JIKAN_PARSER_VERSION', Versions::getVersion('jikan-me/jikan'));


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

$app->register(Jenssegers\Mongodb\MongodbServiceProvider::class);

$app->withFacades();
$app->withEloquent();

$app->configure('swagger-lume');

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
//    'blacklist' => App\Http\Middleware\Blacklist::class,
//    'slave-auth' => App\Http\Middleware\SlaveAuthentication::class,
//    'meta' => App\Http\Middleware\Meta::class,
//    'cache-resolver' => App\Http\Middleware\CacheResolver::class,
//    'throttle' => App\Http\Middleware\Throttle::class,
//    'etag' => \App\Http\Middleware\EtagMiddleware::class,
//    'microcaching' => \App\Http\Middleware\MicroCaching::class,
//    'database-resolver' => \App\Http\Middleware\DatabaseResolver::class,
    'source-data-manager' => \App\Http\Middleware\SourceDataManager::class,
//    'source-health-monitor' => \App\Http\Middleware\SourceHeartbeatMonitor::class
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
$app->configure('controller-to-table-mapping');
$app->configure('controller');

$app->register(\SwaggerLume\ServiceProvider::class);

if (env('CACHING')) {
    $app->configure('cache');
    $app->register(Illuminate\Redis\RedisServiceProvider::class);
}

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);

$guzzleClient = new \GuzzleHttp\Client([
    'timeout' => env('SOURCE_TIMEOUT', 5),
    'connect_timeout' => env('SOURCE_CONNECT_TIMEOUT', 5)
]);
$app->instance('GuzzleClient', $guzzleClient);

$jikan = new \Jikan\MyAnimeList\MalClient(app('GuzzleClient'));
$app->instance('JikanParser', $jikan);

$app->instance('SerializerV4', SerializerFactory::createV4());

$app->register(\App\Providers\SourceHeartbeatProvider::class);


/**
 * Load Blacklist into Redis
 */
//\App\HttpV3\Middleware\Blacklist::loadList(); causing issues on high load todo: add it as a one time init

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
//    'blacklist',
//    'slave-auth',
//    'meta',
//    'etag',
//    'database-resolver',
//    'microcaching',
//    'cache-resolver',
//    'throttle'
//    'source-health-monitor'
//    'source-data-manager'
];


$app->router->group(
    [
        'prefix' => 'v4-alpha',
        'namespace' => env('SOURCE') === 'local' ? 'App\Http\Controllers\V4DB' : 'App\Http\Controllers\V4',
        'middleware' => $commonMiddleware
    ],
    function ($router) {
        require __DIR__.'/../routes/web.v4.php';
    }
);

$app->router->group(
    [
        'prefix' => '/',
    ],
    function ($router) {
        $router->get('/', function () {
            return response()->json([
                'author_url' => 'https://github.com/irfan-dahir',
                'discord_url' => 'http://discord.jikan.moe',
                'version' => '4.0 Alpha Preview',
                'parser_version' => JIKAN_PARSER_VERSION,
                'website_url' => 'https://jikan.moe',
                'documentation_url' => 'https://docs.api.jikan.moe/',
                'github_url' => 'https://github.com/jikan-me/jikan-rest',
                'parser_github_url' => 'https://github.com/jikan-me/jikan',
                'production_api_url' => 'https://api.jikan.moe/v4-alpha/',
                'status_url' => 'https://status.jikan.moe'
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
                    'message' => 'This version is discontinued. Please check the documentation for supported version(s).',
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
                    'message' => 'This version is discontinued. Please check the documentation for supported version(s).',
                    'error' => null
                ], 400);
        });
    }
);

$app->router->group(
    [
        'prefix' => 'v3',
    ],
    function ($router) {
        $router->get('/', function () {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'HttpException',
                    'message' => 'This version is discontinued. Please check the documentation for supported version(s).',
                    'error' => null
                ], 400);
        });
    }
);


return $app;
