<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;

class MangaControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/manga/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data' => [
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
                'title',
                'title_english',
                'title_japanese',
                'title_synonyms',
                'type',
                'chapters',
                'volumes',
                'status',
                'publishing',
                'published' => [
                    'from',
                    'to',
                    'prop' => [
                        'from' => [
                            'day',
                            'month',
                            'year'
                        ],
                        'to' => [
                            'day',
                            'month',
                            'year'
                        ]
                    ],
                    'string'
                ],
                'score',
                'scored_by',
                'rank',
                'popularity',
                'members',
                'favorites',
                'synopsis',
                'background',
                'authors' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url'
                    ]
                ],
                'serializations' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url'
                    ]
                ],
                'genres' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url'
                    ]
                ]
            ]]);
    }

    public function testCharacters()
    {
        $this->get('/v4/manga/1/characters')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'character' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url'
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                            ],
                        ],
                        'name',
                    ],
                    'role',
                ]
            ]]);
    }

    public function testNews()
    {
        $this->get('/v4/manga/1/news')
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
                        'title',
                        'date',
                        'author_username',
                        'author_url',
                        'forum_url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'comments',
                        'excerpt'
                    ]
                ]
            ]);
    }

    public function testPictures()
    {
        $this->get('/v4/manga/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'jpg' => [
                            'image_url',
                            'large_image_url',
                            'small_image_url',
                        ],
                        'webp' => [
                            'image_url',
                            'large_image_url',
                            'small_image_url',
                        ],
                    ]
                ]
            ]);
    }

    public function testStats()
    {
        $this->get('/v4/manga/1/statistics')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'reading',
                'completed',
                'on_hold',
                'dropped',
                'plan_to_read',
                'total',
                'scores' => [
                    [
                        'score',
                        'votes',
                        'percentage'
                    ]
                ]
            ]]);
    }

    public function testForum()
    {
        $this->get('/v4/manga/1/forum')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'date',
                        'author_username',
                        'author_url',
                        'comments',
                        'last_comment' => [
                            'url',
                            'author_username',
                            'author_url',
                            'date'
                        ]
                    ]
                ]
            ]);
    }

    public function testMoreInfo()
    {
        $this->get('/v4/manga/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'moreinfo'
            ]]);
    }

    public function testReviews()
    {
        $this->get('/v4/manga/1/reviews')
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
                        'user' => [
                            'url',
                            'username',
                            'images' => [
                                'jpg' => [
                                    'image_url',
                                ],
                                'webp' => [
                                    'image_url',
                                ],
                            ],
                        ]
                    ]
                ]
            ]);

        $this->get('/v4/manga/1/reviews?page=100')
            ->seeStatusCode(404)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => []
            ]);
    }

    public function testRecommendations()
    {
        $this->get('/v4/manga/1/recommendations')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
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
                        'url',
                        'votes',
                    ]
                ]
            ]);
    }

    public function testUserUpdates()
    {
        $this->get('/v4/manga/1/userupdates')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'user' => [
                            'username',
                            'url',
                            'images' => [
                                'jpg' => [
                                    'image_url',
                                ],
                                'webp' => [
                                    'image_url',
                                ],
                            ],
                        ],
                        'score',
                        'status',
                        'volumes_read',
                        'volumes_total',
                        'chapters_read',
                        'chapters_total',
                        'date'
                    ]
                ]
            ]);

        $this->get('/v4/manga/1/userupdates?page=200')
            ->seeStatusCode(404);
    }

    public function testMangaRelations()
    {
        $this->get('/v4/manga/1/relations')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'relation',
                        'entry' => [
                            [
                                'mal_id',
                                'type',
                                'name',
                                'url'
                            ]
                        ],
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v4/manga/1000000')
            ->seeStatusCode(404);
    }
}
