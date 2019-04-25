<?php

namespace App\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Jikan\Exception\BadResponseException;
use Jikan\Exception\ParserException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Predis\Connection\ConnectionException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {

        // ConnectionException from Redis server
        if ($e instanceof ConnectionException) {
            return response()
                ->json([
                    'status' => 500,
                    'type' => 'ConnectionException',
                    'message' => 'Failed to communicate with Redis',
                    'error' => env('APP_DEBUG') ?  $e->getMessage() : null,
                ], 500);
        }

        // ParserException from Jikan PHP API
        if ($e instanceof ParserException) {
            return response()
                ->json([
                        'status' => 500,
                        'type' => 'ParserException',
                        'message' => 'Unable to parse this request. Please create an issue on GitHub with the request URL and exception error',
                        'error' => $e->getMessage(),
                    ], 500);
        }

        // BadResponseException from Guzzle dep via Jikan PHP API
        // This is basically the response MyAnimeList returns to Jikan
        if ($e instanceof BadResponseException) {
            switch ($e->getCode()) {
                case 404:
                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Resource does not exist',
                            'error' => $e->getMessage()
                        ], $e->getCode());
                case 429:
                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Jikan is being rate limited by MyAnimeList',
                            'error' => $e->getMessage()
                        ], $e->getCode());
                default:
                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Something went wrong, please try again later',
                            'error' => $e->getMessage()
                        ], $e->getCode());
            }
        }

        // Bad REST API requests
        if ($e instanceof HttpException) {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'HttpException',
                    'message' => 'Invalid or incomplete request. Please double check the request documentation',
                    'error' => null
                ], 400);
        }

        if ($e instanceof Exception) {
            return response()
                ->json([
                    'status' => 500,
                    'type' => "Exception",
                    'message' => 'Unhandled Exception. Please create an issue on GitHub with the request URL and exception error',
                    'trace' => "{$e->getFile()} at line {$e->getLine()}",
                    'error' => $e->getMessage()
                ], 400);
        }


    }
}
