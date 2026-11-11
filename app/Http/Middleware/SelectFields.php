<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the opt-in `?fields=` projection to every v4 JSON response with a
 * top-level `data` payload.
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

        /** @var Response $response */
        $response = $next($request);

        if (!$response instanceof JsonResponse || !$response->isSuccessful()) {
            return $response;
        }

        $payload = $response->getData(true);
        if (!is_array($payload) || !array_key_exists('data', $payload)) {
            return $response;
        }

        $requested = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        $available = $this->availableKeys($payload['data']);

        // On an empty result the response has no shape to inspect; fall back to
        // the known-field registry so typos still fail instead of silently passing.
        $known = $available !== [] ? $available : (array) config('response_fields.known', []);
        $unknown = array_values(array_diff($requested, $known));
        if ($known !== [] && $unknown !== []) {
            throw ValidationException::withMessages([
                'fields' => 'Unknown field(s): '.implode(', ', $unknown).'.',
            ]);
        }

        $payload['data'] = $this->project($payload['data'], array_flip($requested));
        $response->setData($payload);

        return $response;
    }

    /**
     * @return array<int, string>
     */
    private function availableKeys(mixed $data): array
    {
        if (!is_array($data)) {
            return [];
        }

        if (!array_is_list($data)) {
            return array_keys($data);
        }

        $keys = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                $keys = array_merge($keys, array_keys($item));
            }
        }

        return array_values(array_unique($keys));
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
