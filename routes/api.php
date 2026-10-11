<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (Jikan v4 + discontinued version stubs)
|--------------------------------------------------------------------------
| The v4 route table lives in routes/web.v4.php and is included below.
| It still uses string controller actions ("Controller@method") together
| with the group "namespace" attribute, which Laravel still supports.
*/

$commonMiddleware = [
    'source-health-monitor',
    'microcaching',
    'cache-ttl',
];

Route::get('/', function () {
    return response()->json([
        'author_url' => 'https://github.com/irfan-dahir',
        'discord_url' => 'http://discord.jikan.moe',
        'version' => config('app.version'),
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
            'last_downtime' => \App\Providers\SourceHeartbeatProvider::getLastDowntime(),
        ],
    ]);
});

Route::redirect('/docs', '/v4/api/documentation', 301);

foreach (['v1', 'v2', 'v3'] as $version) {
    Route::prefix($version)->get('/', function () {
        return response()->json([
            'status' => 400,
            'type' => 'HttpException',
            'message' => 'This version is discontinued. Please check the documentation for supported version(s).',
            'error' => null,
        ], 400);
    });
}

Route::group([
    'prefix' => 'v4',
    'namespace' => 'App\Http\Controllers\V4DB',
    'middleware' => array_merge($commonMiddleware, ['throttle:v4']),
], function ($router) {
    require __DIR__.'/web.v4.php';
});
