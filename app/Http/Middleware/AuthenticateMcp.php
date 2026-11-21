<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Static bearer-token guard for the read-only MCP endpoint (`/mcp`).
 *
 * Fails closed: when MCP_API_KEY is missing or empty the endpoint is unusable
 * and every request is rejected. The token is compared in constant time.
 */
final class AuthenticateMcp
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = config('mcp.api_key');

        if (!is_string($configured) || trim($configured) === '') {
            return $this->unauthorized();
        }

        $header = $request->header('Authorization');

        if (!is_string($header) || !preg_match('/^Bearer\s+(\S+)\s*$/i', $header, $matches)) {
            return $this->unauthorized();
        }

        if (!hash_equals($configured, $matches[1])) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    private function unauthorized(): Response
    {
        return response()->json(
            ['error' => 'Unauthorized'],
            401,
            ['WWW-Authenticate' => 'Bearer realm="mcp"'],
        );
    }
}
