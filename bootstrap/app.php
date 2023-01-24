<?php

use App\Http\Middleware\SourceHeartbeatMonitor;
use App\Providers\SerializerFactory;
use PackageVersions\Versions;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../app/Support/helpers.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
    Defines
*/
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

$app->instance('path.config', app()->basePath() . DIRECTORY_SEPARATOR . 'config');
$app->instance('path.storage', app()->basePath() . DIRECTORY_SEPARATOR . 'storage');

$app->withEloquent();
$app->configure('swagger-lume');
$app->configure('scout');

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

$globalMiddleware = [];

if (env('INSIGHTS', false)) {
    $globalMiddleware[] = \App\Http\Middleware\Insights::class;
}

$app->middleware($globalMiddleware);

$app->routeMiddleware([
    'microcaching' => \App\Http\Middleware\MicroCaching::class,
    'source-health-monitor' => SourceHeartbeatMonitor::class,
    'cache-ttl' => \App\Http\Middleware\EndpointCacheTtlMiddleware::class
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


$app->configure('cache');
$app->configure('database');
$app->configure('queue');
$app->configure('controller-to-table-mapping');
$app->configure('controller');
$app->configure('roadrunner');
$app->configure('data');
$app->configure('jikan');

$app->register(\pushrbx\LumenRoadRunner\ServiceProvider::class);
$app->register(\SwaggerLume\ServiceProvider::class);
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
$app->register(\App\Providers\SourceHeartbeatProvider::class);
$app->register(\App\Providers\EventServiceProvider::class);
$app->register(\App\Providers\AppServiceProvider::class);
$app->register(Illuminate\Redis\RedisServiceProvider::class);
$app->register(Spatie\LaravelData\LaravelDataServiceProvider::class);
$app->register(\App\Providers\JikanEnumServiceProvider::class);

if (env('REPORTING') && strtolower(env('REPORTING_DRIVER')) === 'sentry') {
    $app->register(\Sentry\Laravel\ServiceProvider::class);
    // Sentry Performance Monitoring (optional)
    $app->register(\Sentry\Laravel\Tracing\ServiceProvider::class);

    \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
        $scope->setTag('rest.jikan.version', env('APP_VERSION'));
        $scope->setTag('parser.jikan.version', JIKAN_PARSER_VERSION);
    });
}

$httpClient = \Symfony\Component\HttpClient\HttpClient::create(
    [
        'timeout' => env('SOURCE_TIMEOUT', 1)
    ]
);
$app->instance('HttpClient', $httpClient);

$jikan = new \Jikan\MyAnimeList\MalClient(app('HttpClient'));
$app->instance('JikanParser', $jikan);

$app->instance('SerializerV4', SerializerFactory::createV4());
$app->register(Laravel\Scout\ScoutServiceProvider::class);

// we support TypeSense and ElasticSearch as search indexes.
if (env("SCOUT_DRIVER") === "typesense") {
    // in this case the TYPESENSE_HOST env var should be set too
    $app->register(\Typesense\LaravelTypesense\TypesenseServiceProvider::class);
}
if (env("SCOUT_DRIVER") === "Matchish\ScoutElasticSearch\Engines\ElasticSearchEngine") {
    // in this case the ELASTICSEARCH_HOST env var should be set too
    $app->register(\Matchish\ScoutElasticSearch\ElasticSearchServiceProvider::class);
}


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
    'source-health-monitor',
    'microcaching',
    'cache-ttl'
];


$app->router->group(
    [
        'prefix' => 'v4',
        'namespace' => 'App\Http\Controllers\V4DB',
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
