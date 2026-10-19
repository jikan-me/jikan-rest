<?php

namespace App\Exceptions;

use App\Events\SourceHeartbeatEvent;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jikan\Exception\BadResponseException as JikanBadResponseException;
use Jikan\Exception\ParserException;
use Predis\Connection\ConnectionException;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Handler
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        BadResponseException::class,
    ];

    /**
     * @var string[]
     */
    protected array $acceptableForReportingDriver = [
        ParserException::class,
    ];

    /**
     * An upstream 404 from MyAnimeList (Jikan\Exception\BadResponseException with code 404)
     * is an expected "resource does not exist" answer, not an error: don't log it.
     * Other upstream codes (429, 5xx, ...) are still reported.
     */
    protected function shouldntReport(\Throwable $e)
    {
        if ($e instanceof JikanBadResponseException && $e->getCode() === 404) {
            return true;
        }

        return parent::shouldntReport($e);
    }

    /**
     * @throws Exception
     */
    public function report(\Throwable $e): void
    {
        parent::report($e);
    }

    /**
     * @param  Request  $request
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
            if (! config('app.debug')) {
                $githubReport->setError(' ');
            }

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'ConnectionException',
                    'message' => 'Failed to communicate with the Redis server',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                    'report_url' => config('jikan.github_reporting') ? (string) $githubReport : null,
                ], 500);
        }

        // ParserException from Jikan PHP API
        if ($e instanceof ParserException) {
            $githubReport->setRepo(config('jikan.github_api'));

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'ParserException',
                    'message' => 'Unable to parse this request. Please follow report_url to generate an issue on GitHub',
                    'error' => config('app.debug') ? $e->getMessage() : '',
                    'report_url' => config('jikan.github_reporting') ? (string) $githubReport : null,
                ], 500);
        }

        // BadRequestException from Controllers
        if ($e instanceof BadRequestException) {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'BadRequestException',
                    'message' => $e->getMessage(),
                    'error' => null,
                ], 400);
        }

        if ($e instanceof ValidationException) {
            return response()
                ->json([
                    'status' => 400,
                    'type' => 'ValidationException',
                    'messages' => $e->validator->getMessageBag()->getMessages(),
                    'error' => 'Invalid or incomplete request. Make sure your request is correct. https://docs.api.jikan.moe/',
                ], 400);
        }

        // BadResponseException from Jikan PHP API
        // This is basically the response MyAnimeList returns to Jikan
        if ($e instanceof BadResponseException || $e instanceof JikanBadResponseException) {
            // An upstream failure with no real HTTP status (code 0, or anything outside 100-599)
            // would make Response throw "The HTTP status code "0" is not valid". Report it as a bad gateway.
            if ($e->getCode() < 100 || $e->getCode() > 599) {
                return response()
                    ->json([
                        'status' => 502,
                        'type' => 'BadResponseException',
                        'message' => 'Something went wrong, please try again later.',
                        'error' => config('app.debug') ? $e->getMessage() : '',
                    ], 502);
            }

            switch ($e->getCode()) {
                case 404:
                    //                    $this->set404Cache($request, $e);

                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Resource does not exist',
                            'error' => config('app.debug') ? $e->getMessage() : '',
                        ], $e->getCode());
                case 429:
                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'RateLimitException',
                            'message' => 'Jikan is being rate limited by MyAnimeList.',
                            'error' => config('app.debug') ? $e->getMessage() : '',
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
                            'status' => 500,
                            'type' => 'UpstreamException',
                            'message' => 'Request to MyAnimeList.net failed. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond. Please try again later.',
                            'error' => config('app.debug') ? $e->getMessage() : '',
                        ], 500);
                default:
                    return response()
                        ->json([
                            'status' => $e->getCode(),
                            'type' => 'BadResponseException',
                            'message' => 'Something went wrong, please try again later.',
                            'error' => config('app.debug') ? $e->getMessage() : '',
                        ], $e->getCode());
            }
        }

        if ($e instanceof TimeoutException) {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'UpstreamException',
                    'message' => 'Request to MyAnimeList.net timed out ('.config('jikan.source_timeout').' seconds). Please try again later.',
                    'error' => config('app.debug') ? $e->getMessage() : '',
                ], 500);
        }

        if ($e instanceof TransportException) {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'UpstreamException',
                    'message' => 'Request to MyAnimeList.net failed. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond. Please try again later.',
                    'error' => config('app.debug') ? $e->getMessage() : '',
                ], 500);
        }

        if ($e instanceof Exception && $e->getMessage() === 'Undefined index: url') {
            event(new SourceHeartbeatEvent(SourceHeartbeatEvent::BAD_HEALTH, $e->getCode()));

            return response()
                ->json([
                    'status' => 500,
                    'type' => 'UpstreamException',
                    'message' => 'Request to MyAnimeList.net failed. MyAnimeList.net may be down/unavailable, refuses to connect or took too long to respond. Please try again later.',
                    'error' => config('app.debug') ? $e->getMessage() : '',
                ], 500);
        }

        // Bad REST API requests
        if ($e instanceof HttpException) {
            return response()
                ->json([
                    'status' => $e->getStatusCode(),
                    'type' => 'HttpException',
                    'message' => Response::$statusTexts[$e->getStatusCode()],
                    'error' => null,
                ], $e->getStatusCode());
        }

        return response()
            ->json([
                'status' => 500,
                'type' => 'Exception',
                'message' => 'Unhandled Exception. Please follow report_url to generate an issue on GitHub',
                'trace' => config('app.debug') ? "{$e->getFile()} at line {$e->getLine()}" : '',
                'error' => config('app.debug') ? $e->getMessage() : '',
                'report_url' => config('jikan.github_reporting') ? (string) $githubReport : null,
            ], 500);
    }

    private function reportToSentry(Exception|\Throwable $e): void
    {
        if (! app()->bound('sentry')) {
            return;
        }

        foreach ($this->acceptableForReportingDriver as $type) {
            if ($e instanceof $type) {
                app('sentry')->captureException($e);
            }
        }
    }
}
