<?php

namespace App\Support;

use Illuminate\Http\Request;

class HttpHelper
{
    public static function resolveRequestFingerprint(Request $request): string
    {
        return sprintf('request:%s:%s', self::requestType($request), self::getRequestUriHash($request));
    }

    public static function hasError(mixed $response): bool
    {
        return isset($response->original['error']);
    }

    public static function requestType(Request $request): string
    {
        $requestType = $request->segments()[1];
        if (! \in_array($request->segments()[0], ['v1', 'v2', 'v3', 'v4'])) {
            $requestType = $request->segments()[0];
        }

        return $requestType;
    }

    public static function requestAPIVersion(Request $request): int
    {
        return (int) str_replace('v', '', $request->segment(1));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function serializeEmptyObjects(string $requestType, array $data): array
    {
        if (! ($requestType === 'anime' || $requestType === 'manga')) {
            return $data;
        }

        return self::serializeEmptyObjectsControllerLevel($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function serializeEmptyObjectsControllerLevel(array $data): array
    {
        if (isset($data['related']) && \count($data['related']) === 0) {
            $data['related'] = new \stdClass;
        }

        if (isset($data['related'])) {
            $related = $data['related'];
            $data['related'] = [];

            foreach ($related as $relation => $items) {
                $data['related'][] = [
                    'relation' => $relation,
                    'entry' => $items,
                ];
            }
        }

        return $data;
    }

    public static function getRouteName(Request $request): string
    {
        $route = $request->route();
        if (! $route) {
            return 'App\Undefined';
        }

        // Key used by config('jikan.per_endpoint_cache_ttl') and MicroCaching: the
        // route's name, e.g. "v4.anime.main" (assigned in routes/web.v4.php).
        $name = $route->getName();
        if ($name !== null && $name !== '') {
            return $name;
        }

        return $route->getActionName() === 'Closure' ? 'Closure' : 'App\Undefined';
    }

    public static function getRequestUriHash(Request $request): string
    {
        return sha1($request->getRequestUri());
    }
}
