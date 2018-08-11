<?php

namespace App\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Jikan\Exception\ParserException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
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

        if ($e instanceof ParserException) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

//        Bugsnag::notifyException($e);
//        if ($e instanceof HttpResponseException) {
//        } elseif ($e instanceof ModelNotFoundException) {
//            $e = new NotFoundHttpException($e->getMessage(), $e);
//        } elseif ($e instanceof AuthorizationException) {
//            $e = new HttpException(403, $e->getMessage());
//        } elseif ($e instanceof ValidationException && $e->getResponse()) {
//        }

        $fe = FlattenException::create($e);

        //$decorated = $this->decorate($handler->getContent($fe), $handler->getStylesheet($fe));
        return response()->json(['error' => $this->getContent($fe->getStatusCode())], $fe->getStatusCode());

        //return response()->json($this->getContent($fe), $fe->getStatusCode());

        //$response->exception = $e;


        //return $response;

        //if ($e instanceof CustomException) {
        //    return response('Custom Message');
        //}


        //return response(['error' => $e->getMessage()], 400);
        //var_dump(parent::render($request, $e));
        //return parent::render($request, $e);
    }

    public function getContent($statusCode) {
        switch ($statusCode) {
            case 404:
                return 'Invalid or incomplete endpoint';
            case 400:
                return 'Bad Request';
            case 500:
                return 'Server Error';
            default:
                return 'Unknown error';
        }
    }
}
