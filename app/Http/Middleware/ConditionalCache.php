<?php

namespace App\Http\Middleware;

use App\Support\HttpHelper;
use App\Support\JikanConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds weak ETags and per-endpoint Cache-Control to successful GET responses,
 * and answers If-None-Match with a 304 when the representation is unchanged.
 */
final class ConditionalCache
{
    public function __construct(private readonly JikanConfig $jikanConfig) {}

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (!$request->isMethod('GET') || !$response->isSuccessful()) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || $content === '') {
            return $response;
        }

        $etag = 'W/"'.hash('sha256', $content).'"';
        $response->headers->set('ETag', $etag);

        $ttl = $this->jikanConfig->cacheTtlForEndpoint(HttpHelper::getRouteName($request));
        if ($ttl !== null && $ttl > 0) {
            $response->headers->set('Cache-Control', 'public, max-age='.$ttl);
        }

        $ifNoneMatch = $request->headers->get('If-None-Match');
        if ($ifNoneMatch !== null && $this->matches($ifNoneMatch, $etag)) {
            $response->setNotModified();
        }

        return $response;
    }

    private function matches(string $header, string $etag): bool
    {
        foreach (explode(',', $header) as $candidate) {
            if (trim($candidate) === '*') {
                return true;
            }

            if ($this->normalize($candidate) === $this->normalize($etag)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $etag): string
    {
        return trim(preg_replace('/^W\//', '', trim($etag)), '"');
    }
}
