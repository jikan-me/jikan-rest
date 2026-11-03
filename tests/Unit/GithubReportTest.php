<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Exceptions\GithubReport;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The report URL ends up in the JSON body of every 500 response, so it must not
 * carry exception internals unless the app is in debug mode.
 */
final class GithubReportTest extends TestCase
{
    public function test_exception_message_and_file_are_redacted_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $url = (string) $this->report();

        $this->assertStringNotContainsString('secret-xyz', rawurldecode($url));
        $this->assertStringNotContainsString(basename(__FILE__), rawurldecode($url));
        // still useful for triage
        $this->assertStringContainsString('RuntimeException', rawurldecode($url));
        $this->assertStringContainsString('/v4/anime/1', rawurldecode($url));
    }

    public function test_exception_message_and_file_are_included_when_debug_is_on(): void
    {
        config(['app.debug' => true]);

        $url = rawurldecode(urldecode((string) $this->report()));

        $this->assertStringContainsString('secret-xyz', $url);
        $this->assertStringContainsString(basename(__FILE__), $url);
    }

    private function report(): GithubReport
    {
        return GithubReport::make(new \RuntimeException('secret-xyz'), Request::create('/v4/anime/1'));
    }
}
