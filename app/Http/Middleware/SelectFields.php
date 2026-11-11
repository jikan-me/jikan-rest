<?php

namespace App\Http\Middleware;

use App\Support\HttpHelper;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the opt-in `?fields=` projection to v4 detail and search responses.
 *
 * Only top-level keys are selectable; no parameter means no change at all.
 */
final class SelectFields
{
    public function handle(Request $request, Closure $next): Response
    {
        $raw = trim((string) $request->query('fields', ''));
        if ($raw === '') {
            return $next($request);
        }

        $routes = (array) config('response_fields.routes', []);
        $resource = $routes[HttpHelper::getRouteName($request)] ?? null;
        if ($resource === null) {
            return $next($request);
        }

        $allowed = (array) config('response_fields.resources.'.$resource, []);
        $requested = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        $unknown = array_values(array_diff($requested, $allowed));

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'fields' => 'Unknown field(s): '.implode(', ', $unknown).'.',
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        if (!$response instanceof JsonResponse) {
            return $response;
        }

        $payload = $response->getData(true);
        if (is_array($payload) && array_key_exists('data', $payload)) {
            $keys = array_flip($requested);
            $payload['data'] = $this->project($payload['data'], $keys);
            $response->setData($payload);
        }

        return $response;
    }

    /**
     * @param  array<string, int>  $keys
     */
    private function project(mixed $data, array $keys): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if (array_is_list($data)) {
            return array_map(
                fn ($item) => is_array($item) ? array_intersect_key($item, $keys) : $item,
                $data
            );
        }

        return array_intersect_key($data, $keys);
    }
}
