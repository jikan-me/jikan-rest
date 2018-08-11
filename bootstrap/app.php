<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
    Defines
*/
define('BLACKLIST_PATH', __DIR__ . '/../storage/app/blacklist.json');
define('RATE_LIMIT', 5000); // per day
define('CACHE_EXPIRE', 3600 * 24 * 3); // 3 days
define('CACHE_EXPIRE_SEARCH', 3600 * 6); // 6 hours
//define('CACHE_EXPIRE', 4); // 60 seconds | dev
//define('CACHE_EXPIRE_SEARCH', 4); // 60 seconds | dev

define('REST_VERSION', '3.0');
define('SOURCE_VERSION', '2.0.0-rc.1');

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

//$app->withFacades();
//$app->withEloquent();

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

/*$app->middleware([App\Http\Middleware\Meta::class]);
$app->middleware([App\Http\Middleware\Throttle::class]);*/

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

//$app->routeMiddleware([
//    'blacklist' => App\Http\Middleware\Blacklist::class,
//    'meta' => App\Http\Middleware\Meta::class,
//]);

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

//$app->register(Illuminate\Redis\RedisServiceProvider::class);

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

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

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
