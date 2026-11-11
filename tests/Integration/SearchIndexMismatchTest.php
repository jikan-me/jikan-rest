<?php

namespace Tests\Integration;

use App\Anime;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * End-to-end reproduction of #616: the engine has hits but Mongo cannot hydrate
 * them. Requires a live Typesense server.
 */
final class SearchIndexMismatchTest extends TestCase
{
    use SyntheticMongoDbTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('scout.driver') !== 'typesense') {
            $this->markTestSkipped('Typesense engine-backed test — run with SCOUT_DRIVER=typesense.');
        }

        $node = (array) config('scout.typesense.client-settings.nodes.0', []);
        $health = sprintf('%s://%s:%s/health', $node['protocol'] ?? 'http', $node['host'] ?? '127.0.0.1', $node['port'] ?? 8108);
        $context = stream_context_create(['http' => ['timeout' => 2, 'ignore_errors' => true]]);
        if (@file_get_contents($health, false, $context) === false) {
            $this->markTestSkipped('Typesense server is not reachable.');
        }
    }

    public function test_engine_hits_without_mongo_documents_log_a_mismatch(): void
    {
        Anime::factory()->createOne([
            'mal_id' => 98101,
            'title' => 'Engine Only Show',
            'title_english' => 'Engine Only Show',
            'titles' => [['type' => 'Default', 'title' => 'Engine Only Show']],
            'type' => 'TV',
            'status' => 'Finished Airing',
        ]);

        Anime::where('mal_id', 98101)->get()->searchable();

        // Bypass Eloquent events so Scout does not remove the document from the index.
        DB::table('anime')->where('mal_id', 98101)->delete();

        Log::spy();

        $this->getJson('/v4/anime?q=Engine%20Only%20Show')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'search.index_mismatch'
                && $context['engine_hits'] > 0
                && $context['hydrated'] === 0);
    }

    public function test_healthy_engine_and_mongo_do_not_log_a_mismatch(): void
    {
        Anime::factory()->createOne([
            'mal_id' => 98102,
            'title' => 'Healthy Show',
            'title_english' => 'Healthy Show',
            'titles' => [['type' => 'Default', 'title' => 'Healthy Show']],
            'type' => 'TV',
            'status' => 'Finished Airing',
        ]);

        Anime::where('mal_id', 98102)->get()->searchable();

        Log::spy();

        $this->getJson('/v4/anime?q=Healthy%20Show')
            ->assertStatus(200)
            ->assertJsonFragment(['mal_id' => 98102]);

        Log::shouldNotHaveReceived('warning');
    }
}
