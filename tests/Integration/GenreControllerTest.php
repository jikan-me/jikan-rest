<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\Integration;

use App\GenreAnime;
use App\GenreManga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use ScoutFlush;
    use SyntheticMongoDbTransaction;

    public function testAnimeGenre()
    {
        GenreAnime::factory()->createOne();
        $this->get('/v4/genres/anime')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                [
                    'mal_id',
                    'name',
                    'url',
                    'count',
                ],
            ]]);
    }

    public function testMangaGenre()
    {
        GenreManga::factory()->createOne();
        $this->get('/v4/genres/manga')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [
                [
                    'mal_id',
                    'name',
                    'url',
                    'count',
                ],
            ]]);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/genres')
            ->assertStatus(404);
    }
}
