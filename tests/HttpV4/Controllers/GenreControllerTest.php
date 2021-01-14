<?php

class GenreControllerTest extends TestCase
{
    public function testAnimeGenre()
    {
        $this->get('/v4/genres/anime')
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

    public function testMangaGenre()
    {
        $this->get('/v4/genres/manga')
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

    public function test404()
    {
        $this->get('/v4/genres')
            ->seeStatusCode(404);
    }
}
