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
 * jikan v5.0.0-beta.3 changed AnimeCard::getMembers() to return null for a card without a
 * member count (beta.2 raised ParserException, v4 an uncaught \TypeError). The handler must
 * still answer any ParserException that the parser does raise as a ParserException 500
 * (never a generic 500 / crash).
 */
final class HandlerParserExceptionTest extends TestCase
{
    public function test_card_without_member_count_returns_null_members(): void
    {
        $parser = new AnimeCardParser(new Crawler('<div class="seasonal-anime"><div class="title">No members here</div></div>'));

        $this->assertNull($parser->getMembers());
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
