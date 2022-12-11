<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    public function testUserProfile()
    {
        $this->get('/v4/users/nekomata1037')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'mal_id',
                'username',
                'url',
                'images' => [
                    'jpg' => [
                        'image_url'
                    ],
                    'webp' => [
                        'image_url'
                    ]
                ],
                'last_online',
                'gender',
                'birthday',
                'location',
                'joined',
            ]]);
    }

    public function testUserStatistics()
    {
        $this->get('/v4/users/nekomata1037/statistics')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[

                'anime' => [
                     'days_watched',
                     'mean_score',
                     'watching',
                     'completed',
                     'on_hold',
                     'dropped',
                     'plan_to_watch',
                     'total_entries',
                     'rewatched',
                     'episodes_watched'
                 ],
                 'manga' => [
                     'days_read',
                     'mean_score',
                     'reading',
                     'completed',
                     'on_hold',
                     'dropped',
                     'plan_to_read',
                     'total_entries',
                     'reread',
                     'chapters_read',
                     'volumes_read'
                 ],
            ]]);
    }

    public function testUserAbout()
    {
        $this->get('/v4/users/nekomata1037/about')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'about',
            ]]);
    }

    public function testUserFavorites()
    {
        $this->get('/v4/users/nekomata1037/favorites')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'anime' => [
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
                        'title',
                        'type',
                        'start_year'
                    ]
                ],
                'manga' => [
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
                        'title',
                        'type',
                        'start_year'
                    ]
                ],
                'characters' => [
                    [
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
                    ]
                ],
                'people' => [
                    [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'name',
                    ]
                ]

            ]]);
    }



    public function testUserHistory()
    {
        $this->get('/v4/users/purplepinapples/history/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'entry' => [
                            'mal_id',
                            'type',
                            'name',
                            'url'
                        ],
                        'increment',
                        'date'
                    ]
                ]
            ]);

        $this->get('/v4/users/nekomata1037/history/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [

                ]
            ]);
    }

    public function testUserFriends()
    {
        $this->get('/v4/users/nekomata1037/friends')
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
                                    'image_url'
                                ],
                                'webp' => [
                                    'image_url'
                                ]
                            ]
                        ],
                        'last_online',
                        'friends_since'
                    ]
                ]
            ]);

        $this->get('/v4/users/nekomata1037/friends?page=200')
            ->seeStatusCode(200);
    }

    public function testUserRecommendations()
    {
        $this->get('/v4/users/xinil/recommendations')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
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
                        'user'
                    ]
                ]
            ]);

        $this->get('/v4/users/xinil/recommendations?page=200')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                ]
            ]);
    }

    public function testUserReviews()
    {
        $this->get('/v4/users/xinil/reviews')
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
                    ]
                ]
            ]);

        $this->get('/v4/users/xinil/reviews?page=200')
            ->seeStatusCode(200);
    }

    public function testUserClubs()
    {
        $this->get('/v4/users/nekomata1037/clubs')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'name',
                        'url',
                    ]
                ]
            ]);
    }
}
