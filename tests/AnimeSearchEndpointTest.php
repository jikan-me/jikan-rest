<?php

use App\Testing\ScoutFlush;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Anime;

class AnimeSearchEndpointTest extends TestCase
{
    use DatabaseMigrations, ScoutFlush;

    protected array $searchIndexModelCleanupList = ["App\\Anime"];

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
        $overrides = [];
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
            $overrides["score"] = match ($additionalParams["status"]) {
                "complete" => "Completed",
                "airing" => "Currently Airing",
                "upcoming" => "Upcoming"
            };
        }
        $f->create($overrides);
        $parameters = http_build_query([
            "limit" => $limitCount,
            ...$additionalParams
        ]);
        $uri = "/v4/anime?" . $parameters;
        $sut = $this->getJson($uri);
        $content = $sut->response->json();

        $sut->seeStatusCode(200);
        $sut->response->assertJsonPath("pagination.items.count", $limitCount);
        $sut->response->assertJsonPath("pagination.items.total", 25);
        $sut->response->assertJsonPath("pagination.items.per_page", $limitCount);
        $this->assertIsArray($content["data"]);
        $this->assertCount($limitCount, $content["data"]);
    }
}
