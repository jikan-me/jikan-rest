<?php

namespace App\Mcp\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * Minimal adapter that runs an internal v4 request through the real HTTP kernel.
 *
 * MCP tools are thin wrappers over the public v4 contract: dispatching here (as
 * opposed to calling controllers or repositories directly) keeps validation,
 * caching, search degradation and the JSON shape identical to /v4, so the
 * tools can never drift from the API.
 */
final class V4ApiClient
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly Application $app,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     * @return array{status: int, body: array<string, mixed>|null}
     */
    public function get(string $path, array $query = []): array
    {
        $request = Request::create($path, 'GET', $query, server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $originalRequest = $this->app->bound('request') ? $this->app->make('request') : null;

        try {
            $response = $this->kernel->handle($request);
            $body = json_decode((string) $response->getContent(), true);

            return [
                'status' => $response->getStatusCode(),
                'body' => is_array($body) ? $body : null,
            ];
        } finally {
            // Kernel::handle() rebinds the container's request; restore the
            // outer MCP request so later middleware still see it.
            if ($originalRequest !== null) {
                $this->app->instance('request', $originalRequest);
            } else {
                $this->app->forgetInstance('request');
            }

            Facade::clearResolvedInstance('request');
        }
    }

    /**
     * Best-effort error message from a v4 error body (which follows
     * `{status, type, message, error}`), with a generic fallback.
     *
     * @param  array{status: int, body: array<string, mixed>|null}  $result
     */
    public static function errorMessage(array $result, string $fallback): string
    {
        $message = data_get($result['body'], 'message');

        if (is_string($message) && trim($message) !== '') {
            return $message;
        }

        return $fallback;
    }
}
