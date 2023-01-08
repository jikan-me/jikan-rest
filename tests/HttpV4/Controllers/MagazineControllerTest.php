<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;

class MagazineControllerTest extends TestCase
{
    public function testMagazinesListing()
    {
        $this->get('/v4/magazines')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'mal_id',
                    'name',
                    'url',
                    'count'
                ]
            ]]);
    }
}
