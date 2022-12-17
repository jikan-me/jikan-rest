<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Anime;
use App\CarbonDateRange;
use App\Http\QueryBuilder\AnimeSearchQueryBuilder;
use App\Http\QueryBuilder\MediaSearchQueryBuilder;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

    protected function getBaseUri(): string
    {
        return "/v4/anime";
    }

    private function generateFiveSpecificAndTenRandomElementsInDb(array $params): array
    {
        // 10 random elements
        Anime::factory(10)
            ->overrideFromQueryStringParameters($params, true)
            ->create();
        // 5 specific elements
        $f = Anime::factory(5)
            ->overrideFromQueryStringParameters($params);

        $f->create();

        return $f->raw()[0];
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
            [["start_date" => "2012-04-01"]],
            [["start_date" => "2012-04-28"]],
            [["start_date" => "2012-06-05", "page" => 1]],
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

    public function commonParameterProvider(): array
    {
        return [
            [["status" => "airing"]],
            [["status" => "complete"]],
            [["status" => "upcoming"]],
            [["status" => "Airing"]],
            [["status" => "Complete"]],
            [["status" => "Upcoming"]],
            [["max_score" => "8"]],
            [["min_score" => "6"]],
            [["max_score" => "7", "min_score" => "3"]],
            [["rating" => "pg"]],
            [["rating" => "rx"]],
            [["rating" => "r"]],
            [["rating" => "pg13"]],
            [["rating" => "g"]],
            [["rating" => "r17"]],
        ];
    }

    public function invalidScoreParameterProvider(): array
    {
        return [
            [["max_score" => "634638"], 15],
            [["min_score" => "673473"], 0],
            [["max_score" => "72344", "min_score" => "3532325"], 0],
            [["max_score" => 1, "min_score" => 5], 0],
        ];
    }

    public function invalidRatingParameterProvider(): array
    {
        return [
            [["rating" => "6263ssd"], 0],
            [["rating" => "rx1"], 0],
            [["rating" => "pg133"], 0]
        ];
    }

    public function orderByFieldMappingProvider(): array
    {
        $orderByFieldMappings = array_merge(MediaSearchQueryBuilder::ORDER_BY, AnimeSearchQueryBuilder::ORDER_BY);
        $params = [];

        foreach ($orderByFieldMappings as $paramName => $orderByField) {
            $params[] = [$paramName, $orderByField];
        }

        return $params;
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
        Anime::factory( 25)
            ->overrideFromQueryStringParameters($additionalParams)
            ->create();

        $content = $this->getJsonResponse([
            "limit" => $limitCount,
            ...$additionalParams
        ]);

        $this->seeStatusCode(200);
        $this->assertPaginationData($limitCount, 25, $limitCount);
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
        $this->assertPaginationData(15);
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
        $this->assertPaginationData(5);
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
        $this->assertPaginationData(5);
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
        $this->assertPaginationData(5);
        $this->assertGreaterThanOrEqual(0, $paramStartDate->diff($actualStartDate)->days);
        $this->assertLessThanOrEqual(0, $actualEndDate->diff($paramEndDate)->days);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    public function testSearchWithStartDateEqualToParam()
    {
        // we test here whether the filtering works by start date
        // if the start date parameter's value exactly matches
        // with one item in the database.
        // this is mainly focused on mongodb features
        $startDate = "2015-02-01";
        $carbonStartDate = Carbon::parse($startDate);
        Anime::factory(5)->create();
        $f = Anime::factory(1);
        $f->create($f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]));

        $content = $this->getJsonResponse(["start_date" => $startDate]);
        $actualStartDate = Carbon::parse(data_get($content, "data.0.aired.from"));

        $this->seeStatusCode(200);
        $this->assertPaginationData(1);
        $this->assertEquals(0, $carbonStartDate->diff($actualStartDate)->days);
        $this->assertCount(1, $content["data"]);
    }

    public function testSearchWithEndDateEqualToParam()
    {
        // we test here whether the filtering works by start date
        // if the start date parameter's value exactly matches
        // with one item in the database.
        // this is mainly focused on mongodb features
        $endDate = "2015-03-28";
        $carbonEndDate = Carbon::parse($endDate);
        Anime::factory(5)->create();
        $f = Anime::factory(1);
        $f->create($f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2015-01-05"), $carbonEndDate)
        ]));

        $content = $this->getJsonResponse(["end_date" => $endDate]);
        $actualEndDate = Carbon::parse(data_get($content, "data.0.aired.to"));

        $this->seeStatusCode(200);
        $this->assertPaginationData(1);
        $this->assertEquals(0, $carbonEndDate->diff($actualEndDate)->days);
        $this->assertCount(1, $content["data"]);
    }

    /**
     * @dataProvider genresParameterCombinationsProvider
     */
    public function testSearchByGenres($params)
    {
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);

        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->assertPaginationData(5);
        $this->assertIsArray($content["data"]);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    public function testSearchByInvalidStatusParameter()
    {
        $params = [
            "status" => "gibberish"
        ];
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->assertPaginationData(15);
        $this->assertIsArray($content["data"]);
        // it should return all, and disregard the gibberish filter
        $this->assertCount(15, $content["data"]);
    }

    /**
     * @dataProvider invalidScoreParameterProvider
     */
    public function testSearchByInvalidScoreParameters($params, $expectedCount)
    {
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->assertPaginationData($expectedCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($expectedCount, $content["data"]);
    }

    /**
     * @dataProvider commonParameterProvider
     */
    public function testSearchByCommonParams($params)
    {
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);

        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->assertPaginationData(5);
        $this->assertIsArray($content["data"]);
        // we created 5 elements according to parameters, so we expect 5 of them.
        $this->assertCount(5, $content["data"]);
    }

    /**
     * @dataProvider orderByFieldMappingProvider
     */
    public function testOrderByQueryStringParameter(string $paramName, string $orderByField)
    {
        $expectedCount = 3;
        $f = Anime::factory($expectedCount);
        /**
         * @var Collection $items
         */
        $items = $f->createManyWithOrder($orderByField);
        $content = $this->getJsonResponse([
            "order_by" => $paramName
        ]);

        $this->seeStatusCode(200);
        $this->assertPaginationData($expectedCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($expectedCount, $content["data"]);
        $expectedItems = $items->map(fn($elem) => data_get($elem, $orderByField));
        $actualItems = collect($content["data"])->map(fn($elem) => data_get($elem, $orderByField));

        if ($actualItems->first() instanceof Carbon && $expectedItems->first() instanceof Carbon) {
            $expectedItems = $expectedItems->map(fn(Carbon $elem) => $elem->getTimestamp());
            $actualItems = $actualItems->map(fn(Carbon $elem) => $elem->getTimestamp());
        }

        $this->assertEquals(0, $expectedItems->diff($actualItems)->count());
        $this->assertTrue($expectedItems->toArray() === $actualItems->toArray());
    }

    /**
     * @dataProvider invalidRatingParameterProvider
     */
    public function testSearchByInvalidRatingParameters($params, $expectedCount)
    {
        $this->generateFiveSpecificAndTenRandomElementsInDb($params);
        $content = $this->getJsonResponse($params);

        $this->seeStatusCode(200);
        $this->assertPaginationData($expectedCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($expectedCount, $content["data"]);
    }

    public function testTypeSenseSearchPagination()
    {
        // this should test https://github.com/jikan-me/jikan-rest/issues/298
        $title = "awesome anime";
        // typesense api only returns 250 hits max on one page
        Anime::factory(255)->create([
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
        ]);
        Anime::factory(5)->create();

        $content = $this->getJsonResponse([
            "q" => "awesome",
            "page" => 2
        ]);

        $this->seeStatusCode(200);
        $this->assertPaginationData(25, 255);
        $this->assertIsArray($content["data"]);
        $this->assertCount(25, $content["data"]);
        // https://github.com/jikan-me/jikan-rest/issues/298 shows that the array indexes start at 25, not from 0
        $this->assertArrayHasKey(0, $content["data"]);
    }
}
