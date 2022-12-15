<?php
namespace Database\Factories;

use App\CarbonDateRange;
use App\GenreAnime;
use App\Anime;
use App\Testing\JikanDataGenerator;
use Illuminate\Support\Collection;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Carbon;


class AnimeFactory extends JikanModelFactory
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
            "airing" => $status == "Currently Airing",
            "aired" => new CarbonDateRange($aired_from, $aired_to),
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
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("anime", $mal_id))
        ];
    }

    /**
     * Helper function for overriding fields of the model factory based on query string parameters.
     *
     * @param array $additionalParams
     * @param bool $doOpposite
     * @return self
     */
    public function overrideFromQueryStringParameters(array $additionalParams, bool $doOpposite = false): self
    {
        $additionalParams = collect($additionalParams);

        if ($doOpposite) {
            $overrides = $this->getOppositeOverridesFromQueryStringParameters($additionalParams);
        }
        else {
            $overrides = $this->getOverridesFromQueryStringParameters($additionalParams);
        }

        return $this->state($this->serializeStateDefinition($overrides));
    }

    private function getOppositeOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];

        if ($additionalParams->has("type")) {
            $types = [
                "ova" => "OVA",
                "movie" => "Movie",
                "tv" => "TV"
            ];
            $overrides["type"] = $this->faker->randomElement(array_diff(array_keys($types), [$additionalParams["type"]]));
        }

        if ($additionalParams->has("min_score") && !$additionalParams->has("max_score")) {
            $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"]));
        }

        if (!$additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99);
        }

        if ($additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $overrides["score"] = $this->faker->randomElement([
                $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"])),
                $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99)
            ]);
        }

        if ($additionalParams->has("status")) {
            $statuses = [
                "complete" => "Finished Airing",
                "airing" => "Currently Airing",
                "upcoming" => "Upcoming"
            ];

            $overrides["status"] = $this->faker->randomElement(array_diff(array_keys($statuses), [$additionalParams["status"]]));
        }

        if (($additionalParams->has("genres") && $additionalParams->has("genres_exclude")) || (
                !$additionalParams->has("genres") && $additionalParams->has("genres_exclude")
            ) ) {
            $overrides["genres"] = [];
            // use the "genres_exclude" values to add genres to the anime item
            $genreIds = explode(",", $additionalParams["genres_exclude"]);
            if (count($genreIds) > 1) {
                $genreId = $this->faker->randomElement($genreIds);
            } else {
                $genreId = $genreIds[0];
            }

            $m = $this->ensureGenreExists($genreId);
            $overrides["genres"][] = [
                "mal_id" => $m->mal_id,
                "type" => "anime",
                "name" => $m->name,
                "url" => $m->url
            ];
        } else if ($additionalParams->has("genres")) {
            $overrides["genres"] = [];
            // add such genres which are not in the "genres" param
            $genreIds = explode(",", $additionalParams["genres"]);
            $numberOfGenresToAdd = $this->faker->numberBetween(0, 4);
            for ($numberOfGenresAdded = 0; $numberOfGenresAdded <= $numberOfGenresToAdd; $numberOfGenresAdded++) {
                $outboundsGenreId = $this->faker->numberBetween(0, 74);
                while (in_array($outboundsGenreId, $genreIds)) {
                    $outboundsGenreId = $this->faker->numberBetween(0, 74);
                }

                $m = $this->ensureGenreExists($outboundsGenreId);

                $overrides["genres"][] = [
                    "mal_id" => $m->mal_id,
                    "type" => "anime",
                    "name" => $m->name,
                    "url" => $m->url
                ];
            }
        }

        if ($additionalParams->has("start_date") && !empty($additionalParams["start_date"])
            && !$additionalParams->has("end_date")) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $dt = Carbon::parse($startDate)->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides["aired"] = new CarbonDateRange($dt, null);
        }

        if ($additionalParams->has("end_date") && !empty($additionalParams["end_date"])
            && !$additionalParams->has("start_date")) {
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate)->addDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides["aired"] = new CarbonDateRange($from, $to);
        }

        if ($additionalParams->has("start_date") && $additionalParams->has("end_date")
            && !empty($additionalParams["start_date"]) && !empty($additionalParams["end_date"])) {
            $originalFrom = Carbon::parse($this->adaptDateString($additionalParams["start_date"]));
            $originalTo = Carbon::parse($this->adaptDateString($additionalParams["end_date"]));
            $interval = $originalTo->diff($originalFrom);
            $afterOrBefore = $this->faker->randomElement(["after", "before"]);

            $randomDayIntervalValue = $this->faker->numberBetween(8, 90) + $interval->days;

            [$artificialFrom, $artificialTo] = match ($afterOrBefore) {
                "after" => [$originalFrom->addDays($randomDayIntervalValue), $originalTo->addDays($randomDayIntervalValue)],
                "before" => [$originalFrom->subDays($randomDayIntervalValue), $originalTo->subDays($randomDayIntervalValue)]
            };

            $overrides["aired"] = new CarbonDateRange($artificialFrom, $artificialTo);
        }

        return $overrides;
    }

    private function getOverridesFromQueryStringParameters(Collection $additionalParams): array
    {
        $overrides = [];
        // let's make all database items the same type
        if ($additionalParams->has("type")) {
            $overrides["type"] = match ($additionalParams["type"]) {
                "ova" => "OVA",
                "movie" => "Movie",
                default => "TV"
            };
        }

        if ($additionalParams->has("min_score") && !$additionalParams->has("max_score")) {
            $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), 9.99);
        }

        if (!$additionalParams->has("min_score") && $additionalParams->has("max_score")) {
            $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["max_score"]));
        }

        if ($additionalParams->has(["min_score", "max_score"])) {
            $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), floatval($additionalParams["max_score"]));
        }

        if ($additionalParams->has("status")) {
            $overrides["status"] = match ($additionalParams["status"]) {
                "complete" => "Finished Airing",
                "airing" => "Currently Airing",
                "upcoming" => "Upcoming"
            };
        }

        if ($additionalParams->has("genres")) {
            $overrides["genres"] = [];
            $genreIds = explode(",", $additionalParams["genres"]);
            $genreIds = $this->faker->randomElements($genreIds, $this->faker->numberBetween(0, count($genreIds)));
            foreach ($genreIds as $genreId) {
                $m = $this->ensureGenreExists($genreId);

                $overrides["genres"][] = [
                    "mal_id" => $m->mal_id,
                    "type" => "anime",
                    "name" => $m->name,
                    "url" => $m->url
                ];
            }
        }

        if ($additionalParams->has("start_date") && !empty($additionalParams["start_date"])
            && !$additionalParams->has("end_date")) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $dt = Carbon::parse($startDate)->addDays($this->faker->numberBetween(0, 25));
            $overrides["aired"] = new CarbonDateRange($dt, null);
        }

        if ($additionalParams->has("end_date") && !empty($additionalParams["end_date"])
            && !$additionalParams->has("start_date")) {
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate);
            $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
            $overrides["aired"] = new CarbonDateRange($from, $to->subDays($this->faker->numberBetween(0, 25)));
        }

        if ($additionalParams->has(["start_date", "end_date"])
            && !empty($additionalParams["start_date"]) && !empty($additionalParams["end_date"])) {
            $startDate = $this->adaptDateString($additionalParams["start_date"]);
            $from = Carbon::parse($startDate);
            $endDate = $this->adaptDateString($additionalParams["end_date"]);
            $to = Carbon::parse($endDate);

            $overrides["aired"] = new CarbonDateRange($from, $to);
        }

        return $overrides;
    }

    private function ensureGenreExists(int $genreId): GenreAnime
    {
        $m = GenreAnime::query()->firstWhere("mal_id", $genreId);
        if ($m == null) {
            $f = GenreAnime::factory();
            $m = $f->createOne([
                "mal_id" => $genreId
            ]);
        }

        return $m;
    }

    private function adaptDateString($dateStr): string
    {
        $parts = explode("-", $dateStr);
        if (count($parts) === 1) {
            return $parts[0] . "-01-01";
        }

        return $dateStr;
    }
}
