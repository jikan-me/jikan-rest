<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;

use App\Anime;
use App\Enums\AnimeRatingEnum;
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
            "empty query string" => ["score", true, []],
            "query string: ?filter=airing" => ["score", true, ["filter" => "airing"]],
            "query string: ?type=tv" => ["score", true, ["type" => "tv"]],
            "query string: ?type=movie" => ["score", true, ["type" => "movie"]],
            "query string: ?type=ova" => ["score", true, ["type" => "ova"]],
            "query string: ?type=ona" => ["score", true, ["type" => "ona"]],
            "query string: ?type=special" => ["score", true, ["type" => "special"]],
            "query string: ?type=music" => ["score", true, ["type" => "music"]],
            "query string: ?filter=upcoming" => ["members", true, ["filter" => "upcoming"]],
            "query string: ?filter=bypopularity" => ["members", true, ["filter" => "bypopularity"]],
            "query string: ?filter=favorite" => ["favorites", true, ["filter" => "favorite"]]
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
         * No filters / query string parameters -> sorted by score
         * filter = airing                      -> sorted by score
         * type = tv/movie/ova/ona/special      -> sorted by score
         * filter = upcoming                    -> sorted by "popularity" attribute
         * filter = favorites                   -> sorted by "favorites" attribute
         * filter = bypopular                   -> sorted by "members" attribute
         */
        $expectedCount = 3;

        // create a model factory and pin the rating attribute's value to "G - All Ages".
        $f = Anime::factory($expectedCount)
            ->state([
                "rating" => AnimeRatingEnum::g()->label
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

        $expectedItems = $items->map(fn($elem) => data_get($elem, $fieldToOrderBy))->values();
        $actualItems = collect($content["data"])->map(fn($elem) => data_get($elem, $fieldToOrderBy))->values();

        $this->seeStatusCode(200);
        $this->assertPaginationData($expectedCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($expectedCount, $content["data"]);

        $this->assertCollectionsStrictlyEqual($expectedItems, $actualItems);
    }
}
