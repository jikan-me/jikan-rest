<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\Http\Resources\V4\ReviewsResource;
use App\Person;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * jikan v5.0.0-beta.3 / rc.1 made many getters return null instead of throwing (#617, #619).
 * These pin that this API's code paths survive the nulls and that the OpenAPI document says so.
 */
final class Rc1NullSafetyTest extends TestCase
{
    public function test_user_reviews_pagination_tolerates_null_last_visible_page(): void
    {
        $resource = new ReviewsResource(['results' => [], 'last_visible_page' => null, 'has_next_page' => false]);

        $this->assertSame(
            ['pagination' => ['last_visible_page' => null, 'has_next_page' => false], 'data' => []],
            $resource->toArray(request())
        );
    }

    public function test_person_without_member_favorites_is_serializable_and_indexable(): void
    {
        $person = new Person;
        $person->setRawAttributes(['mal_id' => 1, 'name' => 'Someone', 'member_favorites' => null, 'images' => []]);

        $this->assertNull($person->favorites);
        $this->assertSame(0, $person->toSearchableArray()['member_favorites']);

        $bare = new Person;
        $bare->setRawAttributes(['mal_id' => 2, 'name' => 'No key', 'images' => []]);
        $this->assertNull($bare->favorites);
    }

    public function test_genre_indexer_skips_entries_without_mal_id(): void
    {
        $this->bindFakeParser('getAnimeGenres', [
            'genres' => [['mal_id' => null, 'name' => 'Ghost', 'url' => 'x', 'count' => 1], ['mal_id' => 1, 'name' => 'Action', 'url' => 'u', 'count' => 5]],
            'explicit_genres' => [], 'themes' => [], 'demographics' => [],
        ], 'getMangaGenres');
        DB::table('genres_anime')->delete();

        $this->artisan('indexer:genres')->assertExitCode(0);

        $this->assertSame([1], DB::table('genres_anime')->pluck('mal_id')->all());
        DB::table('genres_anime')->delete();
        DB::table('genres_manga')->delete();
    }

    public function test_magazine_indexer_skips_entries_without_mal_id(): void
    {
        $this->bindFakeParser('getMagazines', [
            'magazines' => [['mal_id' => null, 'name' => 'Ghost', 'url' => 'x', 'count' => 1], ['mal_id' => 7, 'name' => 'Jump', 'url' => 'u', 'count' => 5]],
        ]);
        DB::table('magazines')->delete();

        $this->artisan('indexer:common')->assertExitCode(0);

        $this->assertSame([7], DB::table('magazines')->pluck('mal_id')->all());
        DB::table('magazines')->delete();
    }

    public function test_openapi_marks_the_rc1_nullable_fields_and_documents_anime_character_favorites(): void
    {
        Artisan::call('l5-swagger:generate');
        $spec = json_decode((string) file_get_contents(storage_path('api-docs/api-docs.json')), true);
        $s = $spec['components']['schemas'];

        $nullable = static fn (array $p, string $key): bool => ($p[$key]['nullable'] ?? false) === true;

        // user statistics / profile
        foreach (['anime' => ['days_watched', 'mean_score', 'total_entries', 'rewatched', 'episodes_watched'],
            'manga' => ['days_read', 'mean_score', 'total_entries', 'reread', 'chapters_read', 'volumes_read']] as $kind => $fields) {
            foreach ($fields as $f) {
                $this->assertTrue($nullable($s['user_statistics']['properties']['data']['properties'][$kind]['properties'], $f), "user_statistics.$kind.$f");
                $this->assertTrue($nullable($s['user_profile_full']['properties']['statistics']['properties'][$kind]['properties'], $f), "user_profile_full.$kind.$f");
            }
        }
        $this->assertTrue($nullable($s['user_profile_full']['properties'], 'location'));
        $this->assertTrue($nullable($s['user_profile_full']['properties'], 'about'));

        // people, news, lists
        $this->assertTrue($nullable($s['person']['properties'], 'favorites'));
        $this->assertTrue($nullable($s['person_full']['properties'], 'favorites'));
        $this->assertTrue($nullable($s['person_full']['properties']['voices']['items']['properties'], 'role'));
        $this->assertTrue($nullable($s['person_voice_acting_roles']['properties']['data']['items']['properties'], 'role'));
        $this->assertTrue($nullable($s['news']['properties']['data']['items']['properties'], 'date'));
        $this->assertTrue($nullable($s['news']['properties']['data']['items']['properties'], 'mal_id'));
        $this->assertTrue($nullable($s['genre']['properties'], 'mal_id'));
        $this->assertTrue($nullable($s['magazine']['properties'], 'mal_id'));
        $this->assertTrue($nullable($s['producer']['properties'], 'mal_id'));

        // user reviews pagination and users search
        $this->assertTrue($nullable($s['pagination_nullable_last_page']['properties']['pagination']['properties'], 'last_visible_page'));
        $search = json_encode($s['users_search']);
        $this->assertStringContainsString('"last_online":{"description":"Last Online Date ISO8601","type":"string","nullable":true}', $search);

        // #593
        $this->assertArrayHasKey('favorites', $s['anime_characters']['properties']['data']['items']['properties']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function bindFakeParser(string $method, array $payload, ?string $also = null): void
    {
        $parser = new class($method, $also)
        {
            public function __construct(private string $m, private ?string $a) {}

            public function __call(string $name, array $args): object
            {
                return new \stdClass;
            }
        };
        $this->app->instance('JikanParser', $parser);
        $this->app->instance('SerializerV4', new class($payload)
        {
            public function __construct(private array $payload) {}

            public function serialize(mixed $data, string $format): string
            {
                return json_encode($this->payload);
            }
        });
    }
}
