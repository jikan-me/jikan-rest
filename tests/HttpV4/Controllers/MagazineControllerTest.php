<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Magazine;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class MagazineControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testMagazinesListing()
    {
        Magazine::factory(1)->create();
        $test = $this->get('/v4/magazines');
            $test->seeStatusCode(200)
            ->seeJsonStructure(['data' => [
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }
}
