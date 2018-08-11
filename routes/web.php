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
		'uses' => 'AnimeController@request'
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


