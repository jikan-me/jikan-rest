<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\SearchHydrationGuard;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class SearchHydrationGuardTest extends TestCase
{
    public function test_it_logs_when_the_engine_has_hits_but_hydration_is_empty(): void
    {
        Log::spy();

        SearchHydrationGuard::check(24, 0, 1, 25, 'naruto', 'v4.search.anime');

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'search.index_mismatch'
                && $context['engine_hits'] === 24
                && $context['hydrated'] === 0
                && $context['expected'] === 24
                && $context['query'] === 'naruto');
    }

    public function test_it_does_not_log_when_hydration_matches_the_page(): void
    {
        Log::spy();

        SearchHydrationGuard::check(24, 24, 1, 25, 'naruto', 'v4.search.anime');

        Log::shouldNotHaveReceived('warning');
    }

    public function test_it_accounts_for_page_offsets(): void
    {
        Log::spy();

        SearchHydrationGuard::check(10, 0, 2, 25, 'naruto', 'v4.search.anime');

        Log::shouldNotHaveReceived('warning');
    }
}
