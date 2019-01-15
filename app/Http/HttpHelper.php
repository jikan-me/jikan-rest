<?php

namespace App\Http;

use Illuminate\Http\Request;

class HttpHelper
{
    public static function hasError($response): bool
    {
        return isset($response->original['error']);
    }

    public static function requestType(Request $request): string
    {
        $requestType = $request->segments()[1];
        if (!\in_array($request->segments()[0], ['v1', 'v2', 'v3'])) {
            $requestType = $request->segments()[0];
        }

        return $requestType;
    }

    public static function requestCacheExpiry(string $requestType): int
    {
        $requestType = strtoupper($requestType);
        return (int) (env("CACHE_{$requestType}_EXPIRE") ?? env('CACHE_DEFAULT_EXPIRE'));
    }
}