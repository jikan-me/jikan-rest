<?php

class PersonControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/people/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'mal_id',
                'url',
                'website_url',
                'images' => [
                    'jpg' => [
                        'image_url',
                    ],
                ],
                'name',
                'given_name',
                'family_name',
                'alternate_names',
                'birthday',
                'favorites',
                'about',
            ]]);
    }


    public function testAnimeography()
    {
        $this->get('/v4/people/1/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'position',
                    'anime' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                        ],
                        'title'
                    ]
                ]
            ]]);
    }

    public function testMangaography()
    {
        $this->get('/v4/characters/1/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'position',
                    'manga' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                        ],
                        'title'
                    ]
                ]
            ]]);
    }

    public function testSeiyuu()
    {
        $this->get('/v4/people/1/seiyuu')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'role',
                    'anime' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                                'large_image_url'
                            ],
                        ],
                        'title'
                    ],
                    'character' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                            ],
                        ],
                        'name'
                    ]
                ]
            ]]);
    }

    public function testPictures()
    {
        $this->get('/v4/people/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'large',
                        'small',
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v4/people/1000000')
            ->seeStatusCode(404);
    }
}
