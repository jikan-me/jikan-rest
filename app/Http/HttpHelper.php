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

    public static function requestAPIVersion(Request $request) : int
    {
        return (int) str_replace('v', '', $request->segment(1));
    }

    public static function serializeEmptyObjects(string $requestType, array $data)
    {
        if (!($requestType === 'anime' || $requestType === 'manga')) {
            return $data;
        }

        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass();
        }

        if (isset($data['related'])) {
            $related = $data['related'];
            $data['related'] = [];

            foreach ($related as $relation => $items) {
                $data['related'][] = [
                    'relation' => $relation,
                    'items' => $items
                ];
            }
        }

        return $data;
    }

    public static function serializeEmptyObjectsControllerLevel(array $data)
    {
        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass();
        }

        if (isset($data['related'])) {
            $related = $data['related'];
            $data['related'] = [];

            foreach ($related as $relation => $items) {
                $data['related'][] = [
                    'relation' => $relation,
                    'items' => $items
                ];
            }
        }

        return $data;
    }

    public static function requestControllerName(Request $request) : string
    {
        $route = explode('\\', $request->route()[1]['uses']);
        $route = end($route);

        return explode('@', $route)[0];
    }

    public static function getRequestUriHash(Request $request) : string
    {
        return sha1(env('APP_URL') . $request->getRequestUri());
    }

}
