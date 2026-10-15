<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * jikan v5.0.0-rc.2 (#661, #663): an unknown total ("5/?") in the userupdates tables is now null
 * instead of 0, and news pages paginate. These pin that the API passes the nulls through and that
 * the OpenAPI document declares them nullable.
 */
final class Rc2NullTotalsTest extends TestCase
{
    use SyntheticMongoDbTransaction;

    private const USER = [
        'username' => 'Mar-E',
        'url' => 'https://myanimelist.net/profile/Mar-E',
        'images' => ['jpg' => ['image_url' => null], 'webp' => ['image_url' => null]],
    ];

    public function test_anime_userupdates_serialize_unknown_total_as_null(): void
    {
        $doc = $this->dummyResultsDocument('/v4/anime/21/userupdates', 'anime', [[
            'user' => self::USER, 'score' => null, 'status' => 'Watching',
            'episodes_seen' => 5, 'episodes_total' => null, 'date' => '2023-01-31T22:34:00+00:00',
        ]]);
        DB::table('anime_userupdates')->insert($doc);

        $this->get('/v4/anime/21/userupdates')
            ->assertStatus(200)
            ->assertJsonPath('data.0.episodes_seen', 5)
            ->assertJsonPath('data.0.episodes_total', null)
            ->assertJsonPath('pagination.has_next_page', false);
    }

    public function test_manga_userupdates_serialize_unknown_totals_as_null(): void
    {
        $doc = $this->dummyResultsDocument('/v4/manga/2/userupdates', 'manga', [[
            'user' => self::USER, 'score' => 8, 'status' => 'Reading',
            'volumes_read' => 3, 'volumes_total' => null,
            'chapters_read' => null, 'chapters_total' => null, 'date' => '2023-01-31T22:34:00+00:00',
        ]]);
        DB::table('manga_userupdates')->insert($doc);

        $response = $this->get('/v4/manga/2/userupdates')->assertStatus(200);
        $row = $response->json('data.0');
        $this->assertSame(3, $row['volumes_read']);
        foreach (['volumes_total', 'chapters_read', 'chapters_total'] as $key) {
            $this->assertArrayHasKey($key, $row);
            $this->assertNull($row[$key], $key);
        }
    }

    public function test_last_news_page_reports_no_next_page(): void
    {
        $doc = $this->dummyResultsDocument('/v4/anime/21/news', 'anime', [[
            'mal_id' => 1, 'url' => 'https://myanimelist.net/news/1', 'title' => 'T', 'date' => '2020-08-31T14:34:00+00:00',
            'author_username' => 'a', 'author_url' => 'https://myanimelist.net/profile/a',
            'forum_url' => 'https://myanimelist.net/forum/?topicid=1', 'images' => ['jpg' => ['image_url' => null]],
            'comments' => 2, 'excerpt' => 'x',
        ]], false, 3);
        DB::table('anime_news')->insert($doc);

        $this->get('/v4/anime/21/news')
            ->assertStatus(200)
            ->assertJsonPath('pagination.last_visible_page', 3)
            ->assertJsonPath('pagination.has_next_page', false)
            ->assertJsonPath('data.0.comments', 2);
    }

    public function test_openapi_marks_userupdates_totals_nullable(): void
    {
        Artisan::call('l5-swagger:generate');
        $s = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true)['components']['schemas'];

        $items = static fn (string $schema): array => $s[$schema]['allOf'][1]['properties']['data']['items']['properties'];

        foreach (['episodes_seen', 'episodes_total'] as $f) {
            $this->assertTrue($items('anime_userupdates')[$f]['nullable'] ?? false, "anime_userupdates.$f");
        }
        foreach (['volumes_read', 'volumes_total', 'chapters_read', 'chapters_total'] as $f) {
            $this->assertTrue($items('manga_userupdates')[$f]['nullable'] ?? false, "manga_userupdates.$f");
        }
    }
}
