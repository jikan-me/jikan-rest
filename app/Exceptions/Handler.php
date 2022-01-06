<?php

namespace App\Exceptions;

use App\Events\SourceHeartbeatEvent;
use App\Http\HttpHelper;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jikan\Exception\BadResponseException;
use Jikan\Exception\ParserException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Predis\Connection\ConnectionException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Cache;

/**
 * Class Handler
 * @package App\Exceptions
 */
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
        BadResponseException::class
    ];

    /**
     * @param \Throwable $e
     * @throws Exception
     */
    public function report(\Throwable $e)
    {
        parent::report($e);
    }

    /**
     * @param Request $request
     * @param \Throwable $e
     * @return JsonResponse|Response
     */
    public function render($request, \Throwable $e)
    {
        $githubReport = GithubReport::make($e, $request);

        if (app()->bound('sentry') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }

        // ConnectionException from Redis server
        if ($e instanceof ConnectionException) {
            /*
             * Redis
             * Remove sensitive information from production
             */
            if (!env('APP_DEBUG')) {
                $githubReport->setError(' ');
            }

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'ConnectionException',
                    'message' => 'Failed to communicate with Redis.',
                    'error' => env('APP_DEBUG') ?  $e->getMessage() : null,
                    'report_url' => env('GITHUB_REPORTING', true) ? (string) $githubReport : null
                ], 500);
        }

        if ($e instanceof ConnectException) {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

            return response()
                ->json([
                    'status' => $e->getCode(),
                    'type' => 'BadResponseException',
                    'message' => 'Jikan failed to connect to MyAnimeList.net. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond.',
                    'error' => $e->getMessage()
                ], 503);
        }

        // ParserException from Jikan PHP API
        if ($e instanceof ParserException) {
            $githubReport->setRepo(env('GITHUB_API', 'jikan-me/jikan'));
            return response()
                ->json([
                        'status' => 500,
                        'type' => 'ParserException',
                        'message' => 'Unable to parse this request. Please follow report_url to generate an issue on GitHub',
                        'error' => $e->getMessage(),
                        'report_url' => env('GITHUB_REPORTING', true) ? (string) $githubReport : null
                    ], 500);
        }

        // BadResponseException from Guzzle dep via Jikan PHP API
        // This is basically the response MyAnimeList returns to Jikan
        if ($e instanceof BadResponseException || $e instanceof ClientException) {
            switch ($e->getCode()) {
                case 404:
//                    $this->set404Cache($request, $e);

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
                case 403:
                case 500:
                case 501:
                case 502:
                case 503:
                case 504:
                    // Dispatch Bad source health event to prompt database fallback if enabled
                    event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Jikan failed to connect to MyAnimeList.net. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond.',
                            'error' => $e->getMessage()
                        ], 503);
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

        if ($e instanceof TimeoutException) {
            return response()
                ->json([
                    'status' => 408,
                    'type' => 'TimeoutException',
                    'message' => 'Request to MyAnimeList.net timed out (' .env('SOURCE_TIMEOUT', 5) . ' seconds)',
                    'error' => $e->getMessage()
                ], 408);
        }

        if ($e instanceof Exception) {
            if ($e->getMessage() === "Undefined index: url") {
                event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

                return response()
                    ->json([
                        'status' => $e->getCode(),
                        'type' => 'BadResponseException',
                        'message' => 'Jikan failed to connect to MyAnimeList.net. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond. Retry the request!',
                        'error' => $e->getMessage()
                    ], 503);
            }


            return response()
                ->json([
                    'status' => 500,
                    'type' => "Exception",
                    'message' => 'Unhandled Exception. Please follow report_url to generate an issue on GitHub',
                    'trace' => "{$e->getFile()} at line {$e->getLine()}",
                    'error' => $e->getMessage(),
                    'report_url' => env('GITHUB_REPORTING', true) ? (string) $githubReport : null
                ], 500);
        }
    }

    /**
     * @param Request $request
     * @param BadResponseException $e
     */
    private function set404Cache(Request $request, BadResponseException $e)
    {
        if (!env('CACHING') || env('MICROCACHING')) {
            return;
        }

        $fingerprint = "request:404:".sha1(env('APP_URL') . $request->getRequestUri());

        if (Cache::has($fingerprint)) {
            return;
        }

        $routeController = HttpHelper::requestControllerName($request);
        $cacheTtl = env('CACHE_DEFAULT_EXPIRE', 86400);

        if (\in_array($routeController, [
            'AnimeController',
            'MangaController',
            'CharacterController',
            'PersonController'
        ])) {
            $cacheTtl = env('CACHE_404_EXPIRE', 604800);
        }


        Cache::put($fingerprint, $e->getMessage(), $cacheTtl);
    }
}
