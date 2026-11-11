<?php

namespace Tests\Integration;

use Tests\TestCase;

/**
 * Parity-in-suite, minimal: every endpoint documented in the parity harness
 * manifest (`tools/parity/endpoints.txt`) must resolve to a registered route.
 * The file uses concrete paths (e.g. `/v4/anime/1`); we match them against the
 * parameterized route URIs (`anime/{id}`). Catches endpoint drift
 * (documented-but-missing or renamed) without needing a live server.
 */
final class ParityEndpointsContractTest extends TestCase
{
    public function test_every_documented_parity_endpoint_has_a_route(): void
    {
        $routes = app('router')->getRoutes()->getRoutes();
        $routeRegexes = [];
        foreach ($routes as $route) {
            $routeRegexes[] = self::uriToRegex($route->uri());
        }

        $missing = [];
        foreach (file(base_path('tools/parity/endpoints.txt'), FILE_IGNORE_NEW_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            // tokenize "METHOD /path?query" -> path
            $path = trim(substr($line, strpos($line, ' ') + 1));
            $path = strtok($path, '?') ?: $path;
            $path = trim($path, '/');

            $hit = false;
            foreach ($routeRegexes as $re) {
                if (preg_match($re, $path) === 1) {
                    $hit = true;
                    break;
                }
            }
            if (!$hit) {
                $missing[] = $line;
            }
        }

        $this->assertSame([], $missing,
            'documented parity endpoints without a matching route: '.implode(', ', $missing));
    }

    /** Route URI -> anchored regex with {param} -> [^/]+ */
    private static function uriToRegex(string $uri): string
    {
        $segments = explode('/', trim($uri, '/'));
        $out = '';
        foreach ($segments as $segment) {
            if (str_starts_with($segment, '{') && str_ends_with(rtrim($segment, '}'), '?')) {
                $out .= '(?:/[^/]*)?';   // optional param -> slash optional too
            } elseif (str_contains($segment, '{')) {
                $out .= '/[^/]+';
            } else {
                $out .= '/'.preg_quote($segment, '#');
            }
        }

        return '#^'.ltrim($out, '/').'$#';
    }
}
