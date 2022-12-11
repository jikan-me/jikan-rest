<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;


class CharacterControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/characters/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'mal_id',
                'url',
                'images' => [
                    'jpg' => [
                        'image_url',
                    ],
                    'webp' => [
                        'image_url',
                    ],
                ],
                'name',
                'nicknames',
                'favorites',
                'about',
            ]]);
    }

    public function testAnimeography()
    {
        $this->get('/v4/characters/1/anime')
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
                    'role',
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

    public function testVoices()
    {
        $this->get('/v4/characters/1/voices')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'language',
                    'person' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'name'
                    ]
                ]
            ]]);
    }

    public function testPictures()
    {
        $this->get('/v4/characters/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'jpg' => [
                            'image_url',
                        ]
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v4/characters/1000000')
            ->seeStatusCode(404);
    }
}
