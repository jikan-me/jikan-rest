<?php

namespace App\Testing;

use App\Exceptions\CustomTestException;
use App\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use TypeError;

/**
 * An exception handler class which helps to highlight errors during tests.
 * @codeCoverageIgnore
 */
class TestExceptionsHandler extends Handler
{
    /**
     * @throws \Throwable
     * @throws CustomTestException
     */
    public function render($request, \Throwable $e): JsonResponse|Response
    {
        if ($e instanceof CustomTestException) {
            throw $e;
        }

        if ($e instanceof TypeError) {
            throw $e;
        }

        return parent::render($request, $e);
    }
}
