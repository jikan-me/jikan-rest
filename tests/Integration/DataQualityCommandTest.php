<?php

namespace Tests\Integration;

use App\Anime;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class DataQualityCommandTest extends TestCase
{
    use SyntheticMongoDbTransaction;

    public function test_empty_catalogue_is_clean(): void
    {
        $this->assertSame(0, Artisan::call('jikan:data-quality', ['--json' => true]));
        $this->assertSame([], json_decode(Artisan::output(), true));
    }

    public function test_duplicate_ids_are_reported(): void
    {
        Anime::factory()->createOne(['mal_id' => 70001]);
        Anime::factory()->createOne(['mal_id' => 70001]);

        $this->assertSame(1, Artisan::call('jikan:data-quality', ['--json' => true]));
        $checks = collect(json_decode(Artisan::output(), true))->pluck('check');

        $this->assertContains('duplicates:anime.mal_id', $checks);
    }

    public function test_broken_references_are_reported(): void
    {
        Anime::factory()->createOne([
            'mal_id' => 70002,
            'genres' => [999999],
            'producers' => [999999],
        ]);

        $this->assertSame(1, Artisan::call('jikan:data-quality', ['--json' => true]));
        $checks = collect(json_decode(Artisan::output(), true))->pluck('check');

        $this->assertContains('broken_ref:anime.genres->genres_anime', $checks);
        $this->assertContains('broken_ref:anime.producers->producers', $checks);
    }

    public function test_baseline_suppresses_known_counts(): void
    {
        Anime::factory()->createOne(['mal_id' => 70003]);
        Anime::factory()->createOne(['mal_id' => 70003]);

        $path = tempnam(sys_get_temp_dir(), 'dq-baseline-');
        $this->assertSame(0, Artisan::call('jikan:data-quality', ['--write-baseline' => $path]));

        $this->assertSame(0, Artisan::call('jikan:data-quality', [
            '--baseline' => $path,
            '--json' => true,
        ]));
        $this->assertSame([], json_decode(Artisan::output(), true));

        @unlink($path);
    }

    public function test_baseline_still_fails_when_counts_grow(): void
    {
        Anime::factory()->createOne(['mal_id' => 70004]);

        $path = tempnam(sys_get_temp_dir(), 'dq-baseline-');
        $this->assertSame(0, Artisan::call('jikan:data-quality', ['--write-baseline' => $path]));

        Anime::factory()->createOne(['mal_id' => 70005]);
        Anime::factory()->createOne(['mal_id' => 70005]);

        $this->assertSame(1, Artisan::call('jikan:data-quality', [
            '--baseline' => $path,
            '--json' => true,
        ]));

        @unlink($path);
    }
}
