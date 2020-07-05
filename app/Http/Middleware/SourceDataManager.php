<?php

namespace App\Http\Middleware;

use App\Anime;
use App\DatabaseHandler;
use App\Http\Controllers\V4\AnimeController;
use App\Http\Controllers\V4\Controller;
use App\Http\HttpHelper;
use App\Jobs\UpdateDatabaseJob;
use Closure;
use FastRoute\Route;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

use MongoDB\BSON\UTCDateTime;


class SourceDataManager
{

    public function handle(Request $request, Closure $next)
    {

        if (
            $request->header('auth') === env('APP_KEY')
            || empty($request->segments())
            || !isset($request->segments()[1])
            || \in_array('meta', $request->segments())
        ) {
            return $next($request);
        }

        // todo check if mongo enabled

        $routeExpanded = explode(
            '\\',
            $request->route()[1]['uses']
        );
        $route = end($routeExpanded);


//        if (!\in_array($route, self::STORABLE_DATA)) {
//            return $next($request);
//        }
        $fingerprint = HttpHelper::resolveRequestFingerprint($request);
        $table = DatabaseHandler::getMappedTableName($route);
        $document = DB::table($table)->where('request_hash', $fingerprint);
        $documentExists = $document->exists();

        // Data exists
        if ($documentExists) {
            // check expiry and dispatch job request if needed
            $expiry = $document->get('expiresAt');
            dd('document exists!');
            return;
        }

        // If cache does not exist
        if (!$documentExists) {
            $response = $next($request);

            $freshRequest = Request::create(
                env('APP_URL') . $request->getRequestUri(), 'GET', [
                    'auth' => env('APP_KEY')
                ]
        );
            $freshResponse = app()->handle($freshRequest);

            dd($freshResponse);

            // skip DB, start scraping
//            $response = app('GuzzleClient')
//                ->request(
//                    'GET',
//                    env('APP_URL') . $request->getRequestUri(),
//                    [
//                        'headers' => [
//                            'auth' => env('APP_KEY')
//                        ]
//                    ]
//                );
//
//            dd($response);

            if (HttpHelper::hasError($response)) {
                return $response;
            }

//            $results = DB::table($table)->insert(
//                [
//                    'created_at' => new UTCDateTime(),
//                    'modified_at' => new UTCDateTime(),
//                    'request_hash' => $fingerprint
//                ] + $response
//            );
//
//            dd($results);
        }
    }

}
