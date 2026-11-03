<?php

namespace App\Support;

use Http\Client\Exception as HttpClientException;
use Illuminate\Http\Client\ConnectionException;
use Typesense\Exceptions\ConfigError;
use Typesense\Exceptions\HTTPStatus0Error;
use Typesense\Exceptions\ObjectNotFound;
use Typesense\Exceptions\RequestUnauthorized;
use Typesense\Exceptions\ServerError;
use Typesense\Exceptions\ServiceUnavailable;
use Typesense\Exceptions\Timeout;

/**
 * Classifies search-engine failures that may be degraded to an empty result.
 *
 * Only "the engine is unavailable / the collection does not exist yet" qualifies.
 * Authentication/configuration errors (401/403, ConfigError), malformed requests,
 * validation errors and 404s from the application itself must propagate.
 */
final class SearchEngineFailure
{
    public static function isDegradable(\Throwable $e): bool
    {
        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof RequestUnauthorized
                || $current instanceof ConfigError
                || in_array($current->getCode(), [401, 403], true)) {
                return false;
            }
        }

        for ($current = $e; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof ObjectNotFound
                || $current instanceof ServerError
                || $current instanceof ServiceUnavailable
                || $current instanceof Timeout
                || $current instanceof HTTPStatus0Error
                || $current instanceof HttpClientException
                || $current instanceof ConnectionException) {
                return true;
            }
        }

        return false;
    }
}
