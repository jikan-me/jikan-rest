<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Anime;
use App\Testing\JikanDataGenerator;
use MongoDB\BSON\UTCDateTime;


class AnimeFactory extends Factory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anime::class;


    public function definition(): array
    {
        $mal_id = $this->createMalId();
        $title = $this->createTitle();
        $status = $this->faker->randomElement(["Currently Airing", "Completed", "Upcoming"]);
        [$aired_from, $aired_to] = $this->createActiveDateRange($status, "Currently Airing");

        return [
            "mal_id" => $mal_id,
            "url" => $this->createUrl($mal_id, "anime"),
            "titles" => [
                [
                    "type" => "Default",
                    "title" => $title
                ]
            ],
            "title" => $title,
            "title_english" => $title,
            "title_japanese" => $title,
            "title_synonyms" => [$title],
            "type" => $this->faker->randomElement(["TV", "Movie", "OVA"]),
            "source" => $this->faker->randomElement(["Manga", "Original", "Novel"]),
            "episodes" => $this->faker->randomElement([1, 12, 13, 16, 24, 48, 96, 128, 366]),
            "status" => $status,
            "airing" => $status == "Currently Airing",
            "aired" => [
                "from" => $aired_from->toAtomString(),
                "to" => $aired_to,
            ],
            "duration" => "",
            "rating" => $this->faker->randomElement(["R - 17+ (violence & profanity)", "PG"]),
            "score" => $this->faker->randomFloat(2, 1.00, 9.99),
            "scored_by" => $this->faker->randomDigitNotNull(),
            "rank" => $this->faker->randomDigitNotNull(),
            "popularity" => $this->faker->randomDigitNotNull(),
            "members" => $this->faker->randomDigitNotNull(),
            "favorites" => $this->faker->randomDigitNotNull(),
            "synopsis" => "test",
            "background" => "test",
            "premiered" => $this->faker->randomElement(["Winter", "Spring", "Fall", "Summer"]),
            "broadcast" => [
                "day" => "",
                "time" => "",
                "timezone" => "Asia/Tokyo",
                "string" => "Tuesdays at 00:00 (JST)"
            ],
            "producers" => [
                [
                    "mal_id" => 16,
                    "type" => "anime",
                    "name" => "TV Tokyo",
                    "url" => "https://myanimelist.net/anime/producer/16/TV_Tokyo"
                ]
            ],
            "lincesors" => [
                [
                    "mal_id" => 119,
                    "type" => "anime",
                    "name" => "VIZ Media",
                    "url" => "https://myanimelist.net/anime/producer/119/VIZ_Media"
                ]
            ],
            "studios" => [],
            "genres" => [
                [
                    "mal_id" => 1,
                    "type" => "anime",
                    "name" => "Action",
                    "url" => "https://myanimelist.net/anime/genre/1/Action"
                ]
            ],
            "explicit_genres" => [],
            "themes" => [],
            "demographics" => [
                [
                    "mal_id" => 27,
                    "type" => "anime",
                    "name" => "Shounen",
                    "url" => "https://myanimelist.net/anime/genre/27/Shounen"
                ]
            ],
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4", sha1("http://localhost-test/v4/anime/" . $mal_id))
        ];
    }
}
