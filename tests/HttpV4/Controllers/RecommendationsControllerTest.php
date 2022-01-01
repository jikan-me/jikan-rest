<?php

class RecommendationsControllerTest extends TestCase
{

    public function testAnimeRecommendations()
    {
        $this->get('/v4/recommendations/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'hast_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'entry' => [
                            [
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
                        ],
                        'content',
                        'date',
                        'user' => [
                            'username',
                            'url',
                        ],
                    ]
                ]
            ]);
    }

}