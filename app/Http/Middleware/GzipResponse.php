<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Compresses JSON/text responses with gzip when the client asks for it
 * (Accept-Encoding: gzip). Only touches 2xx responses over 1 KB; skips
 * streamed/binary responses and already-encoded bodies. Adds
 * Content-Encoding + Vary so caches/proxies behave.
 */
class GzipResponse
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            return $response;
        }
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }
        $accept = $request->headers->get('Accept-Encoding', '');
        if (! str_contains($accept, 'gzip')) {
            return $response;
        }

        $body = $response->getContent();
        if (! is_string($body) || strlen($body) < 1024) {
            return $response;
        }

        $compressed = gzencode($body, 6);
        if ($compressed === false) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', 'gzip');
        $response->headers->set('Vary', 'Accept-Encoding');
        $response->headers->set('Content-Length', (string) strlen($compressed));

        return $response;
    }
}