<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Anime;
use App\Enums\AnimeRatingEnum;
use App\Enums\MangaTypeEnum;
use App\Manga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Jikan\Helper\Constants;
use Tests\TestCase;

/**
 * The search index stores isSfw() as a precomputed "sfw" field, while MongoDB queries use sfwExclusions().
 * Both must select the same items, otherwise sfw results differ between engines.
 */
class SfwFilterTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchIndexModelCleanupList = ['App\\Anime', 'App\\Manga'];
    }

    /**
     * @return list<array{mal_id: int, type: string, name: string, url: string}>
     */
    private static function genre(int $malId): array
    {
        return [['mal_id' => $malId, 'type' => 'anime', 'name' => 'x', 'url' => 'x']];
    }

    /**
     * @param  class-string<Anime|Manga>  $modelClass
     */
    private function assertMongoFilterMatchesIsSfw(string $modelClass): void
    {
        $expected = $modelClass::all()->filter(fn ($item) => $item->isSfw())->pluck('mal_id')->sort()->values()->all();
        $actual = $modelClass::query()->exceptItemsWithAdultRating()->pluck('mal_id')->sort()->values()->all();

        $this->assertNotEmpty($expected);
        $this->assertNotEquals($modelClass::count(), count($expected));
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $modelClass::query()->random(100, true)->pluck('mal_id')->sort()->values()->all());
    }

    public function testAnimeSfwFilterAgreesWithIsSfw(): void
    {
        Anime::factory()->createOne(['mal_id' => 1, 'rating' => AnimeRatingEnum::pg13()->label]);
        Anime::factory()->createOne(['mal_id' => 2, 'rating' => AnimeRatingEnum::rx()->label]);
        Anime::factory()->createOne(['mal_id' => 3, 'rating' => AnimeRatingEnum::r17()->label, 'genres' => self::genre(Constants::GENRE_ANIME_HENTAI)]);
        Anime::factory()->createOne(['mal_id' => 4, 'rating' => AnimeRatingEnum::r17()->label, 'explicit_genres' => self::genre(Constants::GENRE_ANIME_EROTICA)]);

        $this->assertMongoFilterMatchesIsSfw(Anime::class);
        $this->assertTrue(Anime::query()->where('mal_id', 4)->firstOrFail()->isSfw(), 'Erotica is allowed as sfw for anime');
    }

    public function testMangaSfwFilterAgreesWithIsSfw(): void
    {
        Manga::factory()->createOne(['mal_id' => 1, 'type' => MangaTypeEnum::manga()->label]);
        Manga::factory()->createOne(['mal_id' => 2, 'type' => MangaTypeEnum::doujin()->label]);
        Manga::factory()->createOne(['mal_id' => 3, 'type' => MangaTypeEnum::manga()->label, 'genres' => self::genre(Constants::GENRE_MANGA_HENTAI)]);

        $this->assertMongoFilterMatchesIsSfw(Manga::class);
    }
}
