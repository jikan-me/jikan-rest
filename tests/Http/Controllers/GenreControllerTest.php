<?php

class GenreControllerTest extends TestCase
{
    public function testAnimeGenre()
    {
        $this->get('/v3/genre/anime/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_url' => [
                    'mal_id',
                    'type',
                    'name',
                    'url'
                ],
                'item_count',
                'anime' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'image_url',
                        'synopsis',
                        'type',
                        'airing_start',
                        'episodes',
                        'members',
                        'genres' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'source',
                        'producers' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'score',
                        'licensors',
                        'r18',
                        'kids'
                    ]
                ]
            ]);
    }

    public function testMangaGenre()
    {
        $this->get('/v3/genre/manga/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_url' => [
                    'mal_id',
                    'type',
                    'name',
                    'url'
                ],
                'item_count',
                'manga' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'image_url',
                        'synopsis',
                        'type',
                        'publishing_start',
                        'volumes',
                        'members',
                        'genres' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'authors' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                        'score',
                        'serialization',
//                        'r18', todo ?
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v3/genre/anime/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/genre/manga/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/genre/anime/100')
            ->seeStatusCode(404);
        $this->get('/v3/genre/manga/100')
            ->seeStatusCode(404);
    }
}
