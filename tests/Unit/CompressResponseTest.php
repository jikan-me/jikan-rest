<?php

namespace Tests\Unit;

use App\Http\Middleware\CompressResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class CompressResponseTest extends TestCase
{
    private function body(int $bytes = 4096): string
    {
        return json_encode(['data' => str_repeat('jikan ', intdiv($bytes, 6))]);
    }

    private function compress(string $acceptEncoding, ?string $body = null, ?CompressResponse $middleware = null, int $status = 200): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/v4/anime/1');
        if ($acceptEncoding !== '') {
            $request->headers->set('Accept-Encoding', $acceptEncoding);
        }
        $content = $body ?? $this->body();

        return ($middleware ?? new CompressResponse)->handle($request, fn () => new Response($content, $status));
    }

    private function withoutBrotli(): CompressResponse
    {
        return new class extends CompressResponse
        {
            protected function brotliAvailable(): bool
            {
                return false;
            }
        };
    }

    private function requireBrotli(): void
    {
        if (!function_exists('brotli_compress')) {
            $this->markTestSkipped('ext-brotli is not installed');
        }
    }

    public function test_brotli_is_preferred_when_available(): void
    {
        $this->requireBrotli();
        $original = $this->body();
        $response = $this->compress('gzip, deflate, br', $original);

        $this->assertSame('br', $response->headers->get('Content-Encoding'));
        $this->assertSame($original, \call_user_func('brotli_uncompress', $response->getContent()));
        $this->assertSame((string) strlen($response->getContent()), $response->headers->get('Content-Length'));
        $this->assertStringContainsString('Accept-Encoding', (string) $response->headers->get('Vary'));
    }

    public function test_gzip_is_used_when_brotli_extension_is_missing(): void
    {
        $original = $this->body();
        $response = $this->compress('gzip, deflate, br', $original, $this->withoutBrotli());

        $this->assertSame('gzip', $response->headers->get('Content-Encoding'));
        $this->assertSame($original, gzdecode($response->getContent()));
        $this->assertStringContainsString('Accept-Encoding', (string) $response->headers->get('Vary'));
    }

    public function test_gzip_is_used_when_client_does_not_accept_brotli(): void
    {
        $response = $this->compress('gzip');

        $this->assertSame('gzip', $response->headers->get('Content-Encoding'));
    }

    public function test_q_zero_marks_a_coding_as_not_acceptable(): void
    {
        $this->assertNull($this->withoutBrotli()->negotiate('gzip;q=0'));
        $this->assertSame('gzip', $this->withoutBrotli()->negotiate('br, gzip;q=0.5'));
        $this->assertSame('gzip', (new CompressResponse)->negotiate('br;q=0, gzip'));
        $this->assertNull((new CompressResponse)->negotiate('br;q=0, gzip;q=0'));
        $this->assertNull((new CompressResponse)->negotiate('*;q=0'));
        $this->assertNull((new CompressResponse)->negotiate('identity'));

        $response = $this->compress('br;q=0, gzip;q=0');
        $this->assertFalse($response->headers->has('Content-Encoding'));
        $this->assertStringContainsString('Accept-Encoding', (string) $response->headers->get('Vary'));
    }

    public function test_q_values_and_wildcard_are_respected(): void
    {
        $this->requireBrotli();
        $middleware = new CompressResponse;

        $this->assertSame('gzip', $middleware->negotiate('br;q=0.4, gzip;q=0.8'));
        $this->assertSame('br', $middleware->negotiate('br;q=0.9, gzip;q=0.8'));
        $this->assertSame('br', $middleware->negotiate('*'));
        $this->assertSame('br', $middleware->negotiate('GZIP, BR'));
        $this->assertSame('gzip', $middleware->negotiate('gzip, *;q=0'));
    }

    public function test_no_accept_encoding_leaves_body_uncompressed_but_sets_vary(): void
    {
        $original = $this->body();
        $response = $this->compress('', $original);

        $this->assertFalse($response->headers->has('Content-Encoding'));
        $this->assertSame($original, $response->getContent());
        $this->assertStringContainsString('Accept-Encoding', (string) $response->headers->get('Vary'));
    }

    public function test_existing_vary_header_is_kept(): void
    {
        $request = Request::create('/v4/anime/1');
        $request->headers->set('Accept-Encoding', 'gzip');
        $response = (new CompressResponse)->handle($request, fn () => new Response($this->body(), 200, ['Vary' => 'Origin']));

        $vary = $response->getVary();
        $this->assertContains('Origin', $vary);
        $this->assertContains('Accept-Encoding', $vary);
    }

    public function test_small_bodies_are_skipped(): void
    {
        $response = $this->compress('gzip, br', '{"data":[]}');

        $this->assertFalse($response->headers->has('Content-Encoding'));
        $this->assertSame('{"data":[]}', $response->getContent());
        $this->assertFalse($response->headers->has('Vary'));
    }

    public function test_non_2xx_responses_are_skipped(): void
    {
        $response = $this->compress('gzip, br', null, null, 404);

        $this->assertFalse($response->headers->has('Content-Encoding'));
    }

    public function test_already_encoded_and_streamed_responses_are_skipped(): void
    {
        $request = Request::create('/v4/anime/1');
        $request->headers->set('Accept-Encoding', 'gzip, br');

        $encoded = (new CompressResponse)->handle($request, fn () => new Response($this->body(), 200, ['Content-Encoding' => 'identity']));
        $this->assertSame('identity', $encoded->headers->get('Content-Encoding'));

        $streamed = (new CompressResponse)->handle($request, fn () => new StreamedResponse(fn () => print ('x')));
        $this->assertFalse($streamed->headers->has('Content-Encoding'));
    }

    public function test_aliases_and_api_routes_use_the_middleware(): void
    {
        $aliases = app(Kernel::class)->getRouteMiddleware();
        $this->assertSame(CompressResponse::class, $aliases['compress']);
        $this->assertSame(CompressResponse::class, $aliases['gzip'], 'legacy alias kept for custom route files');

        $route = app('router')->getRoutes()->match(Request::create('/v4/anime/1'));
        $this->assertContains('compress', $route->gatherMiddleware());
    }
}
