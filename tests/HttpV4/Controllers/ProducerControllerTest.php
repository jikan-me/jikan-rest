<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;

class ProducerControllerTest extends TestCase
{

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
