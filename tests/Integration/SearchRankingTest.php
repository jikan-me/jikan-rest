<?php

namespace Tests\Integration;

use App\Anime;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

/**
 * Fixture-backed ranking checks for the Typesense search path.
 *
 * Run with:
 *   SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd TYPESENSE_HOST=127.0.0.1 \
 *   TYPESENSE_PORT=8108 vendor/bin/phpunit --filter SearchRankingTest
 */
final class SearchRankingTest extends TestCase
{
    use SyntheticMongoDbTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('scout.driver') !== 'typesense') {
            $this->markTestSkipped('Typesense engine-backed test — run with SCOUT_DRIVER=typesense.');
        }

        $host = (string) config('scout.typesense.client-settings.host');
        $port = (int) config('scout.typesense.client-settings.port');
        $context = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        if (@file_get_contents("http://{$host}:{$port}/health", false, $context) === false) {
            $this->markTestSkipped('Typesense server is not reachable.');
        }
    }

    public function test_exact_id_and_title_rank_first(): void
    {
        $this->seedAnime();

        $this->getJson('/v4/anime?q=1')->assertStatus(200)->assertJsonPath('data.0.mal_id', 1);
        $this->getJson('/v4/anime?q=Cowboy%20Bebop')->assertStatus(200)->assertJsonPath('data.0.mal_id', 1);
        $this->getJson('/v4/anime?q=Fullmetal%20Alchemist%3A%20Brotherhood')->assertStatus(200)->assertJsonPath('data.0.mal_id', 5114);
    }

    public function test_alias_and_typo_queries_find_the_expected_title(): void
    {
        $this->seedAnime();

        $this->getJson('/v4/anime?q=FMA%20Brotherhood')
            ->assertStatus(200)
            ->assertJsonFragment(['mal_id' => 5114]);

        $this->getJson('/v4/anime?q=cowboy%20bebp')
            ->assertStatus(200)
            ->assertJsonFragment(['mal_id' => 1]);
    }

    public function test_filtered_search_keeps_filter_semantics(): void
    {
        $this->seedAnime();

        $this->getJson('/v4/anime?q=one%20piece&type=tv&status=complete')
            ->assertStatus(200)
            ->assertJsonFragment(['mal_id' => 21]);
    }

    private function seedAnime(): void
    {
        $fixtures = [
            [1, 'Cowboy Bebop', 'Cowboy Bebop', 'カウボーイビバップ', ['Cowboy Bebop']],
            [5114, 'Fullmetal Alchemist: Brotherhood', 'Fullmetal Alchemist: Brotherhood', '鋼の錬金術師 FULLMETAL ALCHEMIST', ['FMA Brotherhood']],
            [16498, 'Shingeki no Kyojin', 'Attack on Titan', '進撃の巨人', ['Attack on Titan']],
            [21, 'One Piece', 'One Piece', 'ワンピース', ['One Piece']],
        ];

        foreach ($fixtures as [$id, $title, $english, $japanese, $synonyms]) {
            Anime::factory()->createOne([
                'mal_id' => $id,
                'title' => $title,
                'title_english' => $english,
                'title_japanese' => $japanese,
                'titles' => [
                    ['type' => 'Default', 'title' => $title],
                    ...array_map(fn (string $synonym) => ['type' => 'Synonym', 'title' => $synonym], $synonyms),
                ],
                'type' => 'TV',
                'status' => $id === 21 ? 'Finished Airing' : 'Finished Airing',
            ]);
        }

        Anime::whereIn('mal_id', [1, 21, 5114, 16498])->get()->searchable();
    }
}
