<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class ProducerControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testProducersListing()
    {
        $this->get('/v4/producers')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'mal_id',
                    'url',
                    'titles',
                    'images',
                    'count',
                    'favorites',
                    'established',
                    'about',
                    'count'
                ]
            ]]);
    }

}
