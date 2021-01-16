<?php

class ReviewsControllerTest extends TestCase
{

    public function testAnimeReviews()
    {
        $this->get('/v4/reviews/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'hast_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'type',
                        'votes',
                        'date',
                        'review',
                        'episodes_watched',
                        'scores' => [
                            'overall',
                            'story',
                            'animation',
                            'sound',
                            'character',
                            'enjoyment',
                        ],
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
                        'user' => [
                            'username',
                            'url',
                            'images' => [
                                'jpg' => [
                                    'image_url'
                                ],
                                'webp' => [
                                    'image_url'
                                ]
                            ]
                        ],
                    ]
                ]
            ]);
    }

    public function testMangaReviews()
    {
        $this->get('/v4/reviews/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'hast_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'type',
                        'votes',
                        'date',
                        'review',
                        'chapters_read',
                        'scores' => [
                            'overall',
                            'story',
                            'art',
                            'character',
                            'enjoyment',
                        ],
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
                        'user' => [
                            'username',
                            'url',
                            'images' => [
                                'jpg' => [
                                    'image_url'
                                ],
                                'webp' => [
                                    'image_url'
                                ]
                            ]
                        ],
                    ]
                ]
            ]);
    }
}