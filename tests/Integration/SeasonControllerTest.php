<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;

use App\Anime;
use App\CarbonDateRange;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Carbon;
use Tests\TestCase;


class SeasonControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    private function continuingUrlProvider(): array
    {
        return [
            "?continuing=true" => ["/v4/seasons/2024/winter?continuing=true"],
            "?continuing" => ["/v4/seasons/2024/winter?continuing"],
        ];
    }

    public function testShouldFilterOutAnimeWithGarbledAiredString()
    {
        Carbon::setTestNow(Carbon::parse("2024-01-11"));
        // the wrong item
        $f = Anime::factory(1);
        $startDate = "2024-01-01";
        $carbonStartDate = Carbon::parse($startDate);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]);
        $state["aired"]["string"] = "2024 to ?";
        $state["premiered"] = null;
        $state["status"] = "Not yet aired";
        $state["airing"] = false;
        $f->create($state);

        // the correct item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2024-01-10"), Carbon::parse("2024-02-15"))
        ]);
        $state["premiered"] = "Winter 2024";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        $content = $this->getJsonResponse([], "/v4/seasons/2024/winter");

        Carbon::setTestNow();

        $this->seeStatusCode(200);
        $this->assertIsArray($content["data"]);
        $this->assertCount(1, $content["data"]);
    }

    public function testShouldNotFilterOutFutureAiringDates()
    {
        Carbon::setTestNow(Carbon::parse("2024-01-11"));
        // an item in the future airing
        $f = Anime::factory(1);
        $startDate = "2024-02-24";
        $carbonStartDate = Carbon::parse($startDate);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]);
        $state["aired"]["string"] = "Feb 24, 2024 to ?";
        $state["premiered"] = null;
        $state["status"] = "Not yet aired";
        $state["airing"] = false;
        $f->create($state);

        // the "garbled" wrong item
        $f = Anime::factory(1);
        $startDate = "2024-01-01";
        $carbonStartDate = Carbon::parse($startDate);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]);
        $state["aired"]["string"] = "2024 to ?";
        $state["premiered"] = null;
        $state["status"] = "Not yet aired";
        $state["airing"] = false;
        $f->create($state);

        // the absolutely correct item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2024-01-10"), Carbon::parse("2024-02-15"))
        ]);
        $state["premiered"] = "Winter 2024";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        $content = $this->getJsonResponse([], "/v4/seasons/2024/winter");
        $this->seeStatusCode(200);
        $this->assertIsArray($content["data"]);
        $this->assertCount(2, $content["data"]);
    }

    /**
     * @return void
     * @dataProvider continuingUrlProvider
     */
    public function testShouldNotFilterOutContinuingItemsFromPreviousSeasons($requestUrl)
    {
        Carbon::setTestNow(Carbon::parse("2024-01-11"));
        // an item in the future airing
        $f = Anime::factory(1);
        $startDate = "2024-02-24";
        $carbonStartDate = Carbon::parse($startDate);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]);
        $state["aired"]["string"] = "Feb 24, 2024 to ?";
        $state["premiered"] = null;
        $state["status"] = "Not yet aired";
        $state["airing"] = false;
        $f->create($state);

        // the absolutely correct item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2024-01-10"), Carbon::parse("2024-02-15"))
        ]);
        $state["premiered"] = "Winter 2024";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        // the continuing item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2023-10-10"), null)
        ]);
        $state["premiered"] = "Fall 2023";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        $content = $this->getJsonResponse([], $requestUrl);
        $this->seeStatusCode(200);
        $this->assertIsArray($content["data"]);
        $this->assertCount(3, $content["data"]);
    }

    public function testShouldNotIncludeContinuingItemsByDefault()
    {
        Carbon::setTestNow(Carbon::parse("2024-01-11"));
        // an item in the future airing
        $f = Anime::factory(1);
        $startDate = "2024-02-24";
        $carbonStartDate = Carbon::parse($startDate);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange($carbonStartDate, null)
        ]);
        $state["aired"]["string"] = "Feb 24, 2024 to ?";
        $state["premiered"] = null;
        $state["status"] = "Not yet aired";
        $state["airing"] = false;
        $f->create($state);

        // the absolutely correct item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2024-01-10"), Carbon::parse("2024-02-15"))
        ]);
        $state["premiered"] = "Winter 2024";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        // the continuing item
        $f = Anime::factory(1);
        $state = $f->serializeStateDefinition([
            "aired" => new CarbonDateRange(Carbon::parse("2023-10-10"), null)
        ]);
        $state["premiered"] = "Fall 2023";
        $state["status"] = "Currently Airing";
        $state["airing"] = true;
        $f->create($state);

        $content = $this->getJsonResponse([], "/v4/seasons/2024/winter");
        $this->seeStatusCode(200);
        $this->assertIsArray($content["data"]);
        $this->assertCount(2, $content["data"]);
    }
}
