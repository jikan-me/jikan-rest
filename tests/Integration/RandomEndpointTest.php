<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;

use App\Anime;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

/**
 * Regression tests for the random endpoints.
 *
 * QueryRandom*Handler performs a Mongo $sample over the (empty) collections and
 * used to pass `->first()` = null into a Resource, crashing with "Attempt to
 * read property ... on null". They must 404 in the standard error shape when
 * nothing is indexed, and still return data when entries exist.
 */
class RandomEndpointTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchIndexModelCleanupList = ['App\\Anime'];
    }

    public function testRandomAnimeIsNotFoundWhenNothingIsIndexed(): void
    {
        // test DB is truncated per test → empty anime collection
        $this->get("/v4/random/anime")
            ->assertStatus(404)
            ->assertJsonStructure(['status', 'type']);
    }

    public function testRandomCharacterIsNotFoundWhenNothingIsIndexed(): void
    {
        // covers QueryRandomItemHandler (collection-based) family
        $this->get("/v4/random/characters")
            ->assertStatus(404);
    }

    public function testRandomAnimeReturnsADocumentWhenDataExists(): void
    {
        Anime::factory()->create();

        $this->get("/v4/random/anime")
            ->assertStatus(200)
            ->assertJsonPath('data.mal_id', fn ($v) => is_int($v))
            ->assertJsonPath('data.title', fn ($v) => is_string($v));
    }
}