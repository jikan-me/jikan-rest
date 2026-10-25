<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Exceptions\Handler;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Request;
use Jikan\Exception\BadResponseException as JikanBadResponseException;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * An upstream failure carrying no real HTTP status (code 0, or anything outside 100-599)
 * used to crash the handler with "The HTTP status code "0" is not valid".
 */
final class HandlerInvalidStatusCodeTest extends TestCase
{
    private function render(\Throwable $e): Response
    {
        return $this->app->make(Handler::class)->render(Request::create('/v4/anime/1'), $e);
    }

    /** @return array<string, array{int}> */
    public static function invalidCodes(): array
    {
        return ['zero' => [0], 'below 100' => [99], 'above 599' => [600], 'negative' => [-1]];
    }

    #[DataProvider('invalidCodes')]
    public function test_jikan_bad_response_with_invalid_code_maps_to_502(int $code): void
    {
        $response = $this->render(new JikanBadResponseException('upstream gave nothing', $code));

        $this->assertSame(502, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame(502, $body['status']);
        $this->assertSame('BadResponseException', $body['type']);
        $this->assertArrayHasKey('message', $body);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_guzzle_bad_response_with_status_code_zero_maps_to_502(): void
    {
        $e = new BadResponseException('no status', new PsrRequest('GET', 'https://myanimelist.net/'), new PsrResponse(200), null, []);
        (new \ReflectionProperty(\Exception::class, 'code'))->setValue($e, 0);

        $response = $this->render($e);

        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('BadResponseException', json_decode($response->getContent(), true)['type']);
    }

    public function test_valid_codes_keep_their_mapping(): void
    {
        $this->assertSame(404, $this->render(new JikanBadResponseException('x', 404))->getStatusCode());
        $this->assertSame(429, $this->render(new JikanBadResponseException('x', 429))->getStatusCode());
        $this->assertSame(500, $this->render(new JikanBadResponseException('x', 503))->getStatusCode());
        $this->assertSame(418, $this->render(new JikanBadResponseException('x', 418))->getStatusCode());
    }
}
