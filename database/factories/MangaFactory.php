<?php
namespace Database\Factories;

use App\CarbonDateRange;
use App\Testing\JikanDataGenerator;
use App\Manga;
use Illuminate\Support\Collection;
use MongoDB\BSON\UTCDateTime;

class MangaFactory extends JikanMediaModelFactory
{
    use JikanDataGenerator;

    protected $model = Manga::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $title = $this->createTitle();
        $status = $this->faker->randomElement(["Finished", "Publishing", "Upcoming"]);
        [$published_from, $published_to] = $this->createActiveDateRange($status, "Publishing");

        return [
            "mal_id" => $mal_id,
            "url" => $this->createMalUrl($mal_id, "manga"),
            "images" => [
                'jpg' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.jpg',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.jpg',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.jpg',
                ],
                'webp' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.webp',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.webp',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.webp',
                ],
            ],
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
            "type" => $this->faker->randomElement(["Manga", "Light Novel", "Web Novel"]),
            "chapters" => $this->faker->numberBetween(1, 255),
            "volumes" => $this->faker->numberBetween(0, 55),
            "status" => $status,
            "publishing" => $status === "Finished",
            "published" => new CarbonDateRange($published_from, $published_to),
            "score" => $this->faker->randomFloat(2, 1.00, 9.99),
            "scored_by" => $this->faker->randomDigitNotNull(),
            "rank" => $this->faker->randomDigitNotNull(),
            "popularity" => $this->faker->randomDigitNotNull(),
            "members" => $this->faker->randomDigitNotNull(),
            "favorites" => $this->faker->randomDigitNotNull(),
            "synopsis" => "test",
            "approved" => true,
            "background" => "test",
            "authors" => [
                [
                    "mal_id" => 1874,
                    "type" => "people",
                    "name" => "Arakawa, Hiromu",
                    "url" => "https://myanimelist.net/people/1847/Hiromu_Arakawa"
                ]
            ],
            "serializations" => [
                [
                    "mal_id" => 13,
                    "type" => "manga",
                    "name" => "Shounen Gangan",
                    "url" => "https://myanimelist.net/manga/magazine/13/Shounen_Gangan"
                ]
            ],
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
                    "type" => "manga",
                    "name" => "Shounen",
                    "url" => "https://myanimelist.net/manga/genre/27/Shounen"
                ]
            ],
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("manga", $mal_id))
        ];
    }

    protected function getOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = parent::getOverridesFromQueryStringParameters($additionalParams);

        if ($additionalParams->has("magazines")) {
            $overrides["serializations"] = [];
            $magazineIds = explode(",", $additionalParams["magazines"]);
            foreach ($magazineIds as $magazineId) {
                $overrides["serializations"][] = [
                    "mal_id" => (int)$magazineId,
                    "type" => "manga",
                    "name" => "Magazine {$magazineId}",
                    "url" => "https://myanimelist.net/manga/magazine/{$magazineId}/x"
                ];
            }
        }

        return $overrides;
    }

    protected function getOppositeOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = parent::getOppositeOverridesFromQueryStringParameters($additionalParams);

        if ($additionalParams->has("magazines")) {
            $overrides["serializations"] = [];
            $magazineIds = explode(",", $additionalParams["magazines"]);
            do {
                $randomEls = $this->faker->randomElements([11, 60, 89, 54, 32, 22, 108, 65], $this->faker->numberBetween(1, 3));
            } while (count(array_intersect($randomEls, $magazineIds)) > 0);

            foreach ($randomEls as $magazineId) {
                $overrides["serializations"][] = [
                    "mal_id" => (int)$magazineId,
                    "type" => "manga",
                    "name" => "Magazine {$magazineId}",
                    "url" => "https://myanimelist.net/manga/magazine/{$magazineId}/x"
                ];
            }
        }

        return $overrides;
    }
}
