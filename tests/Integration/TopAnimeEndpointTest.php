<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;

use App\Anime;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TopAnimeEndpointTest extends TestCase
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
        return "/v4/top/anime";
    }

    public function orderByFieldAndParamsData()
    {
        return [
            ["rank", false, []],
            ["rank", false, ["filter" => "airing"]],
            ["rank", false, ["type" => "tv"]],
            ["rank", false, ["type" => "movie"]],
            ["rank", false, ["type" => "ova"]],
            ["rank", false, ["type" => "ona"]],
            ["rank", false, ["type" => "special"]],
            ["rank", false, ["type" => "music"]],
            ["members", true, ["filter" => "upcoming"]],
            ["members", true, ["filter" => "bypopularity"]],
            ["favorites", true, ["filter" => "favorite"]]
        ];
    }

    public function testShouldReturnMethodNotAllowedResponseIfMethodNotAllowed()
    {
        $this->json("POST", "/v4/top/anime", ["title" => "Dum"])
            ->seeStatusCode(405);
    }

    /**
     * @dataProvider orderByFieldAndParamsData
     */
    public function testShouldBeOrderedCorrectly(string $fieldToOrderBy, bool $descendingOrder, array $params)
    {
        /*
         * Test whether the API orders the items correctly. It has to return items in similar order as MAL would
         * their search results.
         * No filters / query string parameters -> sorted by rank
         * filter = airing                      -> sorted by rank
         * type = tv/movie/ova/ona/special      -> sorted by rank
         * filter = upcoming                    -> sorted by "members" attribute
         * filter = favorites                   -> sorted by "favorites" attribute
         * filter = bypopular                   -> sorted by "members" attribute
         */
        $expectedCount = 3;

        // create a model factory and pin the rating attribute's value to "G - All Ages".
        $f = Anime::factory($expectedCount)
            ->state([
                "rating" => "G - All Ages"
            ]);

        // pin attribute values in the model factory and create data in the database
        $queryStringAttributesToOverrideBy = [];
        $queryParams = collect($params);

        if ($queryParams->has("type")) {
            $queryStringAttributesToOverrideBy["type"] = $queryParams->get("type");
        }
        if ($queryParams->has("filter") && in_array($queryParams->get("filter"), ["airing", "upcoming"], true)) {
            $queryStringAttributesToOverrideBy["status"] = $queryParams->get("filter");
        }
        if (count($queryStringAttributesToOverrideBy) > 0) {
            $fo = $f->overrideFromQueryStringParameters($queryStringAttributesToOverrideBy, true);
            if ($fieldToOrderBy === "rank") {
                $fo = $fo->state(new Sequence(
                    fn ($sequence) => ["rank" => $this->faker->unique()->numberBetween(120, 999)]
                ));
            }
            // create items with opposing attributes
            $fo->create();

            $f = $f->overrideFromQueryStringParameters($queryStringAttributesToOverrideBy)->count($expectedCount);
        }

        /**
         * @var Collection $items
         */
        $items = $f->createManyWithOrder($fieldToOrderBy);
        if ($descendingOrder) {
            $items = $items->reverse();
        }
        $content = $this->getJsonResponse($params);

        $expectedItems = $items->map(fn($elem) => data_get($elem, $fieldToOrderBy));
        $actualItems = collect($content["data"])->map(fn($elem) => data_get($elem, $fieldToOrderBy));

        $this->seeStatusCode(200);
        $this->assertPaginationData($expectedCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($expectedCount, $content["data"]);

        $this->assertCollectionsStrictlyEqual($expectedItems, $actualItems);
    }
}
