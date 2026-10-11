<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
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
        $this->get('/v4/magazines')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }
}
