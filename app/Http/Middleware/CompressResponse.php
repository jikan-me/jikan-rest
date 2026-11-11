<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Compresses JSON/text responses with Brotli or gzip, negotiated from the
 * client's Accept-Encoding header (q-values respected; q=0 means "not
 * acceptable"). Brotli is used when the client accepts it at least as much as
 * gzip and ext-brotli is loaded; otherwise gzip. Only 2xx responses of at
 * least 1 KB are touched; streamed/binary responses and already-encoded
 * bodies are skipped. Every response that could have been compressed gets
 * Vary: Accept-Encoding, so shared caches keep the variants apart.
 */
class CompressResponse
{
    private const MIN_BYTES = 1024;

    private const GZIP_LEVEL = 6;

    /** Brotli quality 5: close to gzip -6 in CPU cost, noticeably smaller output. */
    private const BROTLI_QUALITY = 5;

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (!$response instanceof Response
            || $response instanceof StreamedResponse
            || $response instanceof BinaryFileResponse) {
            return $response;
        }
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        $body = $response->getContent();
        if (!is_string($body) || strlen($body) < self::MIN_BYTES) {
            return $response;
        }

        // From here on the representation depends on Accept-Encoding.
        $response->setVary('Accept-Encoding', false);

        $encoding = $this->negotiate((string) $request->headers->get('Accept-Encoding', ''));
        $compressed = match ($encoding) {
            'br' => $this->brotli($body),
            'gzip' => gzencode($body, self::GZIP_LEVEL),
            default => false,
        };
        if (!is_string($compressed)) {
            return $response;
        }

        $response->setContent($compressed);
        $response->headers->set('Content-Encoding', (string) $encoding);
        $response->headers->set('Content-Length', (string) strlen($compressed));

        return $response;
    }

    /**
     * Picks 'br', 'gzip' or null from an Accept-Encoding header.
     */
    public function negotiate(string $header): ?string
    {
        $q = $this->parse($header);
        $wildcard = $q['*'] ?? 0.0;

        $gzip = $q['gzip'] ?? $q['x-gzip'] ?? $wildcard;
        $br = $this->brotliAvailable() ? ($q['br'] ?? $wildcard) : 0.0;

        if ($br > 0 && $br >= $gzip) {
            return 'br';
        }

        return $gzip > 0 ? 'gzip' : null;
    }

    protected function brotliAvailable(): bool
    {
        return function_exists('brotli_compress');
    }

    private function brotli(string $body): string|false
    {
        if (!$this->brotliAvailable()) {
            return false;
        }

        // ext-brotli is optional (absent on some dev/CI PHP builds), so it is
        // called dynamically rather than as a hard symbol for static analysis.
        /** @var string|false $out */
        $out = \call_user_func('brotli_compress', $body, self::BROTLI_QUALITY);

        return $out;
    }

    /**
     * @return array<string, float> lower-cased coding => q-value
     */
    private function parse(string $header): array
    {
        $codings = [];
        foreach (explode(',', $header) as $part) {
            $params = array_map('trim', explode(';', $part));
            $coding = strtolower(array_shift($params));
            if ($coding === '') {
                continue;
            }
            $quality = 1.0;
            foreach ($params as $param) {
                if (preg_match('/^q\s*=\s*([0-9.]+)$/i', $param, $m) === 1) {
                    $quality = max(0.0, min(1.0, (float) $m[1]));
                }
            }
            $codings[$coding] = $quality;
        }

        return $codings;
    }
}
