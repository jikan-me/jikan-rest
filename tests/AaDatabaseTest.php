<?php

use App\Anime;
use Laravel\Lumen\Testing\DatabaseMigrations;

// silly hack to run the database migrations once
// and for the other test cases we keep truncating the collections in mongo.
class AaDatabaseTest extends TestCase
{
    use DatabaseMigrations;

    public function testDatabase()
    {
        $this->assertEquals(0, Anime::count());
    }
}
