<?php
namespace Database\Factories;

use App\CarbonDateRange;
use App\Anime;
use App\Testing\JikanDataGenerator;
use MongoDB\BSON\UTCDateTime;


class AnimeFactory extends JikanMediaModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anime::class;


    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $title = $this->createTitle();
        $status = $this->faker->randomElement(["Currently Airing", "Completed", "Upcoming"]);
        [$aired_from, $aired_to] = $this->createActiveDateRange($status, "Currently Airing");

        return [
            "mal_id" => $mal_id,
            "url" => $this->createMalUrl($mal_id, "anime"),
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
            "airing" => $status === "Currently Airing",
            "aired" => new CarbonDateRange($aired_from, $aired_to),
            "duration" => "",
            "rating" => $this->getRandomRating(),
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
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("anime", $mal_id))
        ];
    }
}
