<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Jikan\Exception\ParserException;
use Jikan\Parser\Common\AnimeCardParser;
use Jikan\Request\Producer\ProducerRequest;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

/**
 * jikan v5.0.0-beta.2: an anime card without a member count raises ParserException from
 * MalClient (via a RuntimeException in AnimeCardParser::getMembers()), where it used to
 * escape as an uncaught \TypeError. The handler must answer it as a ParserException 500
 * (never a generic 500 / crash).
 */
final class HandlerParserExceptionTest extends TestCase
{
    public function test_card_without_member_count_throws_runtime_exception(): void
    {
        $parser = new AnimeCardParser(new Crawler('<div class="seasonal-anime"><div class="title">No members here</div></div>'));

        $this->expectException(\RuntimeException::class);
        $parser->getMembers();
    }

    public function test_parser_exception_from_card_failure_renders_as_parser_exception_500(): void
    {
        $previous = new \RuntimeException('Could not find the member count on the anime card.');
        $e = ParserException::fromRequest(new ProducerRequest(1), $previous);

        $response = $this->app->make(Handler::class)->render(Request::create('/v4/producers/1'), $e);

        $this->assertSame(500, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame(500, $body['status']);
        $this->assertSame('ParserException', $body['type']);
        $this->assertArrayHasKey('report_url', $body);
    }
}
