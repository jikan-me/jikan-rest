<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Unit;

use App\Support\AliasNormalizer;
use Tests\TestCase;

final class AliasNormalizerTest extends TestCase
{
    public function test_it_normalizes_case_and_punctuation(): void
    {
        $this->assertSame('cowboy bebop', AliasNormalizer::normalize('Cowboy Bebop!'));
        $this->assertSame('fma brotherhood', AliasNormalizer::normalize('  FMA: Brotherhood  '));
        $this->assertSame('shingeki no kyojin', AliasNormalizer::normalize('Shingeki no Kyojin'));
    }

    public function test_it_handles_unicode_compatibility_and_whitespace(): void
    {
        $this->assertSame('steins gate', AliasNormalizer::normalize('Steins;Gate'));
        $this->assertSame('your name', AliasNormalizer::normalize('Your  Name'));
        $this->assertSame('86', AliasNormalizer::normalize('８６'));
    }

    public function test_variants_include_compact_form(): void
    {
        $this->assertSame(['cowboy bebop', 'cowboybebop'], AliasNormalizer::variants('Cowboy Bebop'));
    }
}
