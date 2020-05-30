<?php

class MagazineControllerTest extends TestCase
{
    public function testMagazine()
    {
        $this->get('/v3/magazine/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'meta' => [
                    'mal_id',
                    'type',
                    'name',
                    'url'
                ],
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
        $this->get('/v3/magazine/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/magazine/1/1000')
            ->seeStatusCode(404);
        $this->get('/v3/magazine/100000')
            ->seeStatusCode(404);
        $this->get('/v3/magazine/100000')
            ->seeStatusCode(404);
    }
}
