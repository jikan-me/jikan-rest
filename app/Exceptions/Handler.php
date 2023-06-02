<?php

namespace App\Exceptions;

use App\Events\SourceHeartbeatEvent;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jikan\Exception\BadResponseException as JikanBadResponseException;
use GuzzleHttp\Exception\BadResponseException;
use Jikan\Exception\ParserException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Predis\Connection\ConnectionException;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @var string[]
     */
    protected array $acceptableForReportingDriver = [
        ParserException::class
    ];

    /**
     * @param \Throwable $e
     * @throws Exception
     */
    public function report(\Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param Request $request
     * @param \Throwable $e
     * @return JsonResponse|Response
     */
    public function render($request, \Throwable $e): JsonResponse|Response
    {
        $githubReport = GithubReport::make($e, $request);
        $this->reportToSentry($e);

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

        // BadRequestException from Controllers
        if ($e instanceof BadRequestException) {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'BadRequestException',
                    'message' => $e->getMessage(),
                    'error' => null
                ], 400);
        }

        if ($e instanceof ValidationException) {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'ValidationException',
                    'messages' => $e->validator->getMessageBag()->getMessages(),
                    'error' => 'Invalid or incomplete request. Make sure your request is correct. https://docs.api.jikan.moe/'
                ], 400);
        }

        // BadResponseException from Jikan PHP API
        // This is basically the response MyAnimeList returns to Jikan
        if ($e instanceof BadResponseException || $e instanceof JikanBadResponseException) {
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

        if ($e instanceof TimeoutException) {
            return response()
                ->json([
                    'status' => 408,
                    'type' => 'TimeoutException',
                    'message' => 'Request to MyAnimeList.net timed out (' .env('SOURCE_TIMEOUT', 5) . ' seconds)',
                    'error' => $e->getMessage()
                ], 408);
        }

        if ($e instanceof TransportException) {
            return response()
                ->json([
                    'status' => 500,
                    'type' => 'TransportException',
                    'message' => 'Request to MyAnimeList.net has failed. The upstream server has returned a non-successful status code.',
                    'error' => $e->getMessage()
                ], 500);
        }

        if ($e instanceof Exception && $e->getMessage() === "Undefined index: url") {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

            return response()
                ->json([
                    'status' => $e->getCode(),
                    'type' => 'BadResponseException',
                    'message' => 'Jikan failed to connect to MyAnimeList.net. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond. Retry the request!',
                    'error' => $e->getMessage()
                ], 503);
        }

        // Bad REST API requests
        if ($e instanceof HttpException) {
            return response()
                ->json([
                    'status' => $e->getStatusCode(),
                    'type' => 'HttpException',
                    'message' => Response::$statusTexts[$e->getStatusCode()],
                    'error' => null
                ], $e->getStatusCode());
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


    /**
     * @param Exception|\Throwable $e
     * @return void
     */
    private function reportToSentry(\Exception|\Throwable $e): void
    {
        if (!app()->bound('sentry')) {
            return;
        }

        foreach ($this->acceptableForReportingDriver as $type) {
            if ($e instanceof $type) {
                app('sentry')->captureException($e);
            }
        }
    }
}
