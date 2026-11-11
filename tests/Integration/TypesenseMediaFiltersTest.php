<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Anime;
use App\Enums\AnimeRatingEnum;
use App\Enums\MangaTypeEnum;
use App\Manga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Carbon;
use Jikan\Helper\Constants;
use Tests\TestCase;

/**
 * Search filters applied by the search engine (SCOUT_DRIVER=typesense) must select the same items as the database
 * filters. Skips when the driver is not typesense; run with e.g.
 *   SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd TYPESENSE_HOST=127.0.0.1 TYPESENSE_PORT=8108 \
 *   vendor/bin/phpunit --filter TypesenseMediaFiltersTest
 */
final class TypesenseMediaFiltersTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('scout.driver') !== 'typesense') {
            $this->markTestSkipped('Typesense engine-backed test — run with SCOUT_DRIVER=typesense + a reachable server.');
        }
        if (empty(config('scout.typesense.client-settings.api_key'))) {
            $this->markTestSkipped('No Typesense API key configured — cannot run the engine-backed test.');
        }
        $this->searchIndexModelCleanupList = ['App\\Anime', 'App\\Manga'];
    }

    /**
     * @return list<array{mal_id: int, type: string, name: string, url: string}>
     */
    private static function genres(int ...$malIds): array
    {
        return array_map(fn (int $malId) => ['mal_id' => $malId, 'type' => 'anime', 'name' => 'x', 'url' => 'x'], $malIds);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAnime(int $malId, array $overrides = []): void
    {
        // empty themes/demographics: filters must work even when no document has a value for a field
        Anime::factory()->createOne([
            'mal_id' => $malId,
            'title' => "Filterprobe $malId",
            'approved' => true,
            'genres' => [],
            'explicit_genres' => [],
            'themes' => [],
            'demographics' => [],
            ...$overrides,
        ]);
    }

    /**
     * @return list<int>
     */
    private function searchIds(string $url): array
    {
        $response = $this->get($url);
        $response->assertOk();

        return collect($response->json('data'))->pluck('mal_id')->sort()->values()->all();
    }

    public function testGenresRequireAllGivenGenres(): void
    {
        $this->createAnime(1, ['genres' => self::genres(1)]);
        $this->createAnime(2, ['genres' => self::genres(1, 2)]);
        $this->createAnime(3, ['genres' => self::genres(2)]);
        Anime::all()->searchable();

        $this->assertSame([2], $this->searchIds('/v4/anime?q=filterprobe&genres=1,2'));
        $this->assertSame([1, 2], $this->searchIds('/v4/anime?q=filterprobe&genres=1'));
        $this->assertSame([1], $this->searchIds('/v4/anime?q=filterprobe&genres_exclude=2'));
    }

    public function testGenresWorkOnMangaWithoutThemesOrDemographics(): void
    {
        Manga::factory()->createOne(['mal_id' => 1, 'title' => 'Filterprobe 1', 'approved' => true,
            'genres' => self::genres(1), 'themes' => [], 'demographics' => [], 'serializations' => []]);
        Manga::all()->searchable();

        $this->assertSame([1], $this->searchIds('/v4/manga?q=filterprobe&genres=1'));
        $this->assertSame([], $this->searchIds('/v4/manga?q=filterprobe&genres_exclude=1'));
        $this->assertSame([], $this->searchIds('/v4/manga?q=filterprobe&magazines=5'));
        $this->assertSame([], $this->searchIds('/v4/manga?q=filterprobe&magazines=,'));
    }

    public function testNonNumericIdsDoNotReachTheFilterExpression(): void
    {
        $this->createAnime(1, ['genres' => self::genres(1)]);
        $this->createAnime(2, ['genres' => self::genres(1), 'approved' => false]);
        Anime::all()->searchable();

        $this->assertSame([], $this->searchIds('/v4/anime?q=filterprobe&genres=abc'));
        $this->assertSame([], $this->searchIds('/v4/anime?q=filterprobe&producers=abc'));
        $this->assertSame([], $this->searchIds('/v4/anime?q=filterprobe&producers=,'));
        $this->assertSame([1], $this->searchIds('/v4/anime?q=filterprobe&genres='.urlencode('1) || (approved:=false')));
        $this->assertSame([], $this->searchIds('/v4/anime?q=filterprobe&producers='.urlencode('1]) || (approved:=false')));
    }

    public function testEndDateExcludesItemsWithoutEndDate(): void
    {
        $this->createAnime(1, ['aired' => ['from' => '2010-01-01T00:00:00+00:00', 'to' => '2011-01-01T00:00:00+00:00']]);
        $this->createAnime(2, ['aired' => ['from' => '2010-01-01T00:00:00+00:00', 'to' => '2021-01-01T00:00:00+00:00']]);
        $this->createAnime(3, ['aired' => ['from' => '2010-01-01T00:00:00+00:00', 'to' => null]]);
        Anime::all()->searchable();

        $this->assertSame([1], $this->searchIds('/v4/anime?q=filterprobe&end_date=2020-01-01'));
        $this->assertSame([1], Anime::query()->filter(collect(['end_date' => Carbon::parse('2020-01-01')->toImmutable()]))
            ->pluck('mal_id')->sort()->values()->all());
    }

    public function testOrderByIsAppliedByTheEngine(): void
    {
        foreach ([1 => 7.1, 2 => 9.2, 3 => 8.3] as $malId => $score) {
            $this->createAnime($malId, ['score' => $score]);
        }
        Anime::all()->searchable();

        $response = $this->get('/v4/anime?q=filterprobe&order_by=score&sort=desc');
        $response->assertOk();
        $this->assertSame([2, 3, 1], collect($response->json('data'))->pluck('mal_id')->all());
    }

    public function testLimitPagesThroughAllResults(): void
    {
        foreach (range(1, 7) as $malId) {
            $this->createAnime($malId);
        }
        Anime::all()->searchable();

        $pages = collect([1, 2, 3])->map(fn (int $page) => $this->get("/v4/anime?q=filterprobe&limit=3&page=$page")->assertOk());

        $this->assertSame([3, 3, 1], $pages->map(fn ($response) => count($response->json('data')))->all());
        $this->assertSame(7, $pages[0]->json('pagination.items.total'));
        $this->assertSame(range(1, 7), $pages->flatMap(fn ($response) => $response->json('data'))->pluck('mal_id')->sort()->values()->all());
    }

    public function testSfwMatchesIsSfw(): void
    {
        $this->createAnime(1, ['rating' => AnimeRatingEnum::pg13()->label]);
        $this->createAnime(2, ['rating' => AnimeRatingEnum::rx()->label]);
        $this->createAnime(3, ['rating' => AnimeRatingEnum::r17()->label, 'genres' => self::genres(Constants::GENRE_ANIME_HENTAI)]);
        $this->createAnime(4, ['rating' => AnimeRatingEnum::r17()->label, 'explicit_genres' => self::genres(Constants::GENRE_ANIME_HENTAI)]);
        Manga::factory()->createOne(['mal_id' => 1, 'title' => 'Filterprobe 1', 'approved' => true, 'type' => MangaTypeEnum::manga()->label, 'genres' => [], 'explicit_genres' => []]);
        Manga::factory()->createOne(['mal_id' => 2, 'title' => 'Filterprobe 2', 'approved' => true, 'type' => MangaTypeEnum::doujin()->label, 'genres' => [], 'explicit_genres' => []]);
        Anime::all()->searchable();
        Manga::all()->searchable();

        $this->assertSame([1], $this->searchIds('/v4/anime?q=filterprobe&sfw=true'));
        $this->assertSame([1], $this->searchIds('/v4/manga?q=filterprobe&sfw=true'));
    }
}
