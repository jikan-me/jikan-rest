<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Anime;
use App\GenreAnime;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnimeSearchEndpointTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->searchIndexModelCleanupList = ["App\\Anime"];
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

    /**
     * Helper function for overriding fields of model factories based on parameters.
     *
     * @param array $additionalParams
     * @param bool $negate
     * @return array
     */
    private function getFactoryFieldOverrides(array $additionalParams, bool $negate = false): array
    {
        $overrides = [];

        if (!$negate) {
            // let's make all database items the same type
            if (array_key_exists("type", $additionalParams)) {
                $overrides["type"] = match ($additionalParams["type"]) {
                    "ova" => "OVA",
                    "movie" => "Movie",
                    default => "TV"
                };
            }

            if (array_key_exists("min_score", $additionalParams) && !array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), 9.99);
            }

            if (!array_key_exists("min_score", $additionalParams) && array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["max_score"]));
            }

            if (array_key_exists("min_score", $additionalParams) && array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["min_score"]), floatval($additionalParams["max_score"]));
            }

            if (array_key_exists("status", $additionalParams)) {
                $overrides["status"] = match ($additionalParams["status"]) {
                    "complete" => "Finished Airing",
                    "airing" => "Currently Airing",
                    "upcoming" => "Upcoming"
                };
            }

            if (array_key_exists("genres", $additionalParams)) {
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

            if (array_key_exists("start_date", $additionalParams) && !empty($additionalParams["start_date"])
            && !array_key_exists("end_date", $additionalParams)) {
                $startDate = $this->adaptDateString($additionalParams["start_date"]);
                $dt = Carbon::parse($startDate)->addDays($this->faker->numberBetween(0, 25));
                $overrides["aired"]["from"] = $dt->toAtomString();
                // $overrides["aired"]["to"] = $dt->addDays($this->faker->randomElement([30, 60, 90, 120, 180]))->toAtomString();
                $overrides["aired"]["to"] = null;
            }

            if (array_key_exists("end_date", $additionalParams) && !empty($additionalParams["end_date"])
                && !array_key_exists("start_date", $additionalParams)) {
                $endDate = $this->adaptDateString($additionalParams["end_date"]);
                $to = Carbon::parse($endDate);
                $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
                $overrides["aired"]["from"] = $from->toAtomString();
                $overrides["aired"]["to"] = $to->subDays($this->faker->numberBetween(0, 25))->toAtomString();
            }

            if (array_key_exists("start_date", $additionalParams) && array_key_exists("end_date", $additionalParams)
                && !empty($additionalParams["start_date"]) && !empty($additionalParams["end_date"])) {
                $startDate = $additionalParams["start_date"];
                $from = Carbon::parse($startDate);
                $endDate = $additionalParams["end_date"];
                $to = Carbon::parse($endDate);

                $overrides["aired"]["from"] = $from->toAtomString();
                $overrides["aired"]["to"] = $to->toAtomString();
            }
        } else {
            // the opposites
            if (array_key_exists("type", $additionalParams)) {
                $types = [
                    "ova" => "OVA",
                    "movie" => "Movie",
                    "tv" => "TV"
                ];
                $overrides["type"] = $this->faker->randomElement(array_diff(array_keys($types), [$additionalParams["type"]]));
            }

            if (array_key_exists("min_score", $additionalParams) && !array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"]));
            }

            if (!array_key_exists("min_score", $additionalParams) && array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99);
            }

            if (array_key_exists("min_score", $additionalParams) && array_key_exists("max_score", $additionalParams)) {
                $overrides["score"] = $this->faker->randomElement([
                    $this->faker->randomFloat(2, 1.00, floatval($additionalParams["min_score"])),
                    $this->faker->randomFloat(2, floatval($additionalParams["max_score"]), 9.99)
                ]);
            }

            if (array_key_exists("status", $additionalParams)) {
                $statuses = [
                    "complete" => "Finished Airing",
                    "airing" => "Currently Airing",
                    "upcoming" => "Upcoming"
                ];

                $overrides["status"] = $this->faker->randomElement(array_diff(array_keys($statuses), [$additionalParams["status"]]));
            }

            if ((array_key_exists("genres", $additionalParams) && array_key_exists("genres_exclude", $additionalParams)) || (
                    !array_key_exists("genres", $additionalParams) && array_key_exists("genres_exclude", $additionalParams)
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
            } else if (array_key_exists("genres", $additionalParams)) {
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

            if (array_key_exists("start_date", $additionalParams) && !empty($additionalParams["start_date"])
                && !array_key_exists("end_date", $additionalParams)) {
                $startDate = $this->adaptDateString($additionalParams["start_date"]);
                $dt = Carbon::parse($startDate)->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
                $overrides["aired"]["from"] = $dt->toAtomString();
                // $overrides["aired"]["to"] = $dt->addDays($this->faker->randomElement([30, 60, 90, 120, 180]))->toAtomString();
                $overrides["aired"]["to"] = null;
            }

            if (array_key_exists("end_date", $additionalParams) && !empty($additionalParams["end_date"])
                && !array_key_exists("start_date", $additionalParams)) {
                $endDate = $this->adaptDateString($additionalParams["end_date"]);
                $to = Carbon::parse($endDate)->addDays($this->faker->randomElement([30, 60, 90, 120, 180]));
                $from = $to->copy()->subDays($this->faker->randomElement([30, 60, 90, 120, 180]));
                $overrides["aired"]["from"] = $from->toAtomString();
                $overrides["aired"]["to"] = $to->toAtomString();
            }

            if (array_key_exists("start_date", $additionalParams) && array_key_exists("end_date", $additionalParams)
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

                $overrides["aired"]["from"] = $artificialFrom->toAtomString();
                $overrides["aired"]["to"] = $artificialTo->toAtomString();
            }
        }

        return $overrides;
    }

    /**
     * @param array $params
     * @return array
     */
    private function getJsonResponse(array $params): array
    {
        $parameters = http_build_query($params);
        $uri = "/v4/anime?" . $parameters;
        $this->getJson($uri);
        return $this->response->json();
    }

    private function generateFiveSpecificAndTenRandomElementsInDb(array $params): array
    {
        // 10 random elements
        $f = Anime::factory()->count(10);
        $f->create($this->getFactoryFieldOverrides($params, true));
        // 5 specific elements
        $f = Anime::factory()->count(5);
        $overrides = $this->getFactoryFieldOverrides($params);
        $f->create($overrides);

        return $overrides;
    }

    public function limitParameterCombinationsProvider(): array
    {
        return [
            [5, []],
            [5, ["type" => "tv"]],
            [5, ["type" => "tv", "min_score" => 7]],
            [5, ["type" => "tv", "max_score" => 6]],
            [5, ["type" => "tv", "status" => "complete", "max_score" => 8]],
            [5, ["type" => "movie", "status" => "complete", "max_score" => 8]]
        ];
    }

    public function startDatesParameterProvider(): array
    {
        return [
            [["start_date" => "2022"]],
            [["start_date" => "2012-05"]],
            [["start_date" => "2012-05-12"]],
            [["start_date" => "2012-05-12", "page" => 1]],
        ];
    }

    public function endDatesParameterProvider(): array
    {
        return [
            [["end_date" => "2022"]],
            [["end_date" => "2012-05"]],
            [["end_date" => "2012-05-12"]],
            [["end_date" => "2012-05-12", "page" => 1]],
        ];
    }

    public function startAndEndDatesParameterProvider(): array
    {
        return [
            [["start_date" => "2021", "end_date" => "2022"]],
            [["start_date" => "2021-01", "end_date" => "2021-02"]],
            [["start_date" => "2021-01-01", "end_date" => "2021-03-22"]],
            [["start_date" => "2021-01-01", "end_date" => "2021-03-22", "page" => 1]],
        ];
    }

    public function genresParameterCombinationsProvider(): array
    {
        return [
            [["genres" => "1,2"]],
            [["genres_exclude" => "4,5", "type" => "tv"]],
            [["genres" => "1,2", "genres_exclude" => "3", "min_score" => 8, "type" => "tv", "status" => "complete", "page" => 1]],
        ];
    }

    public function emptyDateRangeProvider(): array
    {
        return [
            [["start_date" => ""]],
            [["end_date" => ""]],
            [["end_date" => "", "start_date" => ""]],
        ];
    }

    /**
     * @test
     */
    public function shouldReturnMethodNotAllowedResponseIfMethodNotAllowed()
    {
        $this->json("POST", "/v4/anime", ["title" => "Dum"])
            ->seeStatusCode(405);
    }

    /**
     * @dataProvider limitParameterCombinationsProvider
     */
    public function testLimitParameter(int $limitCount, array $additionalParams)
    {
        $f = Anime::factory()->count(25);
        $f->create($this->getFactoryFieldOverrides($additionalParams));
        $content = $this->getJsonResponse([
            "limit" => $limitCount,
            ...$additionalParams
        ]);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", $limitCount);
        $this->response->assertJsonPath("pagination.items.total", 25);
        $this->response->assertJsonPath("pagination.items.per_page", $limitCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($limitCount, $content["data"]);
    }

    /**
     * @dataProvider emptyDateRangeProvider
     */
    public function testSearchByEmptyDatesShouldDoNothing($params)
    {
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", 15);
        $this->response->assertJsonPath("pagination.items.total", 15);
        $this->response->assertJsonPath("pagination.items.per_page", 25);
        $this->assertCount(15, $content["data"]);
    }

    /**
     * @dataProvider startDatesParameterProvider
     */
    public function testSearchByStartDate($params)
    {
        $overrides = $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $actualStartDate = Carbon::parse(data_get($content, "data.0.aired.from"));
        $paramStartDate = Carbon::parse($overrides["aired"]["from"]);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", 5);
        $this->response->assertJsonPath("pagination.items.total", 5);
        $this->response->assertJsonPath("pagination.items.per_page", 25);
        $this->assertGreaterThanOrEqual(0, $paramStartDate->diff($actualStartDate)->days);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    /**
     * @dataProvider endDatesParameterProvider
     */
    public function testSearchByEndDate($params)
    {
        $overrides = $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $actualEndDate = Carbon::parse(data_get($content, "data.0.aired.to"));
        $paramEndDate = Carbon::parse($overrides["aired"]["to"]);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", 5);
        $this->response->assertJsonPath("pagination.items.total", 5);
        $this->response->assertJsonPath("pagination.items.per_page", 25);
        $this->assertLessThanOrEqual(0, $actualEndDate->diff($paramEndDate)->days);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    /**
     * @dataProvider startAndEndDatesParameterProvider
     */
    public function testSearchByStartAndEndDate($params)
    {
        $overrides = $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $actualStartDate = Carbon::parse(data_get($content, "data.0.aired.from"));
        $paramStartDate = Carbon::parse($overrides["aired"]["from"]);
        $actualEndDate = Carbon::parse(data_get($content, "data.0.aired.to"));
        $paramEndDate = Carbon::parse($overrides["aired"]["to"]);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", 5);
        $this->response->assertJsonPath("pagination.items.total", 5);
        $this->response->assertJsonPath("pagination.items.per_page", 25);
        $this->assertGreaterThanOrEqual(0, $paramStartDate->diff($actualStartDate)->days);
        $this->assertLessThanOrEqual(0, $actualEndDate->diff($paramEndDate)->days);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    /**
     * @dataProvider genresParameterCombinationsProvider
     */
    public function testSearchByGenres($params)
    {
        // 10 random elements
        $f = Anime::factory()->count(10);
        $f->create($this->getFactoryFieldOverrides($params, true));
        // 5 specific elements
        $f = Anime::factory()->count(5);
        $f->create($this->getFactoryFieldOverrides($params));
        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->response->assertJsonPath("pagination.items.count", 5);
        $this->response->assertJsonPath("pagination.items.total", 5);
        $this->response->assertJsonPath("pagination.items.per_page", 25);
        $this->assertIsArray($content["data"]);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }
}
