<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class ReviewsControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeReviews()
    {
        $this->get('/v4/reviews/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'type',
                        'reactions',
                        'date',
                        'review',
                        'score',
                        'tags',
                        'is_spoiler',
                        'is_preliminary',
                        'episodes_watched',
                        'entry' => [
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
                    'has_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'type',
                        'reactions',
                        'date',
                        'review',
                        'score',
                        'tags',
                        'is_spoiler',
                        'is_preliminary',
                        'chapters_read',
                        'entry' => [
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
