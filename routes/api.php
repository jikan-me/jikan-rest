<?php

use App\Providers\SourceHeartbeatProvider;
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
            'status' => SourceHeartbeatProvider::getHeartbeatStatus(),
            'score' => SourceHeartbeatProvider::getHeartbeatScore(),
            'down' => SourceHeartbeatProvider::isFailoverEnabled(),
            'last_downtime' => SourceHeartbeatProvider::getLastDowntime(),
        ],
    ]);
});

// Scalar API reference — rendered from the l5-swagger OpenAPI JSON.
Route::get('/v4/docs', fn () => view('scalar.reference'));

// Canonical OpenAPI spec is served by l5-swagger itself at /v4/docs/openapi
// (routes > docs: 'docs/openapi' in config/l5-swagger.php). The old spec URL
// retains backwards compatibility via 301.
Route::redirect('/v4/api-docs', '/v4/docs/openapi', 301);

// Aliases: point the history and the plain /docs at the Scalar page (instead of
// the disabled Swagger UI).
Route::redirect('/docs', '/v4/docs', 301);
Route::redirect('/v4/documentation', '/v4/docs', 301);

// LLM/machine index (llmstxt.org style) — serves the committed llms.txt.
Route::get('/llms.txt', function () {
    $index = base_path('llms.txt');

    return is_file($index)
        ? response()->file($index, ['Content-Type' => 'text/plain; charset=utf-8'])
        : response()->json(['error' => 'llms.txt not found'], 404);
});

// AI tool-calling manifest (committed canonical at docs/ai/manifest.json;
// regenerate with `composer endpoints:write`, verify with endpoints:check).
Route::get('/v4/ai/manifest.json', function () {
    $manifest = base_path('docs/ai/manifest.json');

    return is_file($manifest)
        ? response()->file($manifest, ['Content-Type' => 'application/json; charset=utf-8'])
        : response()->json(['error' => 'AI manifest not generated — run composer endpoints:write'], 404);
});

// Health/liveness endpoint. Registered explicitly: withRouting(health:) only works
// when no custom `using` closure is provided (we load the v4 routes ourselves).
Route::get('/up', fn () => response('OK', 200));

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
    'middleware' => array_merge($commonMiddleware, ['throttle:v4']),
], function () {
    require __DIR__.'/web.v4.php';
});
