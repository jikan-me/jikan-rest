<?php

namespace Tests\Integration;

use App\Producers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Tests\TestCase;

/**
 * End-to-end search through the real Typesense engine, hydrating entities
 * from MongoDB — the Mongo+Typesense combo, exercised in the suite.
 *
 * Requires a reachable Typesense server: run with, e.g.
 *   SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd TYPESENSE_HOST=127.0.0.1 \
 *   TYPESENSE_PORT=8108 vendor/bin/phpunit --filter TypesenseSearchEndpointTest
 * Skips (self-contained) when the driver is not typesense, so the standard
 * engine-less CI run stays green.
 */
final class TypesenseSearchEndpointTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    private const SEED = [
        ['mal_id' => 9001, 'url' => 'https://myanimelist.net/anime/producer/9001/Studio_One', 'titles' => [['title' => 'Studio One']], 'count' => 42, 'favorites' => 1234, 'established' => null, 'images' => ['jpg' => ['image_url' => 'https://cdn.myanimelist.net/images/p.jpg'], 'webp' => ['image_url' => 'https://cdn.myanimelist.net/images/p.webp']]],
        ['mal_id' => 9002, 'url' => 'https://myanimelist.net/anime/producer/9002/Two_Doors', 'titles' => [['title' => 'Two Doors Studio']], 'count' => 7, 'favorites' => 9, 'established' => null, 'images' => ['jpg' => ['image_url' => 'https://cdn.myanimelist.net/images/p.jpg'], 'webp' => ['image_url' => 'https://cdn.myanimelist.net/images/p.webp']]],
        ['mal_id' => 9003, 'url' => 'https://myanimelist.net/anime/producer/9003/Unrelated', 'titles' => [['title' => 'Unrelated Co']], 'count' => 3, 'favorites' => 1, 'established' => null, 'images' => ['jpg' => ['image_url' => 'https://cdn.myanimelist.net/images/p.jpg'], 'webp' => ['image_url' => 'https://cdn.myanimelist.net/images/p.webp']]],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (config('scout.driver') !== 'typesense') {
            $this->markTestSkipped('Typesense engine-backed test — run with SCOUT_DRIVER=typesense + a reachable server.');
        }
        if (empty(config('scout.typesense.client-settings.api_key'))) {
            $this->markTestSkipped('No Typesense API key configured — cannot run the engine-backed test.');
        }
    }

    public function test_search_returns_mongo_hydrated_results_via_typesense(): void
    {
        foreach (self::SEED as $item) {
            DB::table('producers')->insert(array_merge($item, [
                'createdAt' => new UTCDateTime,
                'modifiedAt' => new UTCDateTime,
            ]));
        }

        try {
            // Import the seeded producers into the Typesense testing index.
            Producers::whereIn('mal_id', array_column(self::SEED, 'mal_id'))->get()->searchable();

            $response = $this->get('/v4/producers?q=studio');
            $response->assertOk();
            $data = $response->json('data');
            $this->assertNotEmpty($data, 'expected at least one Searchable producer result');
            $this->assertContains(9001, array_column($data, 'mal_id'));
            $this->assertContains(9002, array_column($data, 'mal_id'));

            // Results are full Mongo entities, not just ids.
            $found = collect($data)->firstWhere('mal_id', 9001);
            $this->assertNotNull($found['url'] ?? null);

            // Non-matching query returns empty (graceful, engine present).
            $this->get('/v4/producers?q=zzzzzzzz')
                ->assertOk()
                ->assertJsonCount(0, 'data');
        } finally {
            Producers::whereIn('mal_id', array_column(self::SEED, 'mal_id'))->get()->each->unsearchable();
        }
    }
}