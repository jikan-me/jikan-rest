<?php

class MangaControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v3/manga/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_id',
                'url',
                'image_url',
                'title',
                'title_english',
                'title_japanese',
                'title_synonyms',
                'type',
                'volumes',
                'chapters',
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
                'related' => [
                    'Adaptation' => [
                        [
                            'mal_id',
                            'type',
                            'name',
                            'url'
                        ]
                    ],
                    'Side story' => [
                        [
                            'mal_id',
                            'type',
                            'name',
                            'url'
                        ]
                    ],
                    // ...
                ],
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
            ]);
    }

    public function testCharacters()
    {
        $this->get('/v3/manga/1/characters')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'characters' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'name',
                        'role'
                    ]
                ]
            ]);
    }

    public function testNews()
    {
        $this->get('/v3/manga/1/news')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'articles' => [
                    [
                        'url',
                        'title',
                        'date',
                        'author_name',
                        'author_url',
                        'forum_url',
                        'image_url',
                        'comments',
                        'intro'
                    ]
                ]
            ]);
    }

    public function testPictures()
    {
        $this->get('/v3/manga/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pictures' => [
                    [
                        'large',
                        'small',
                    ]
                ]
            ]);
    }

    public function testStats()
    {
        $this->get('/v3/manga/1/stats')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reading',
                'completed',
                'on_hold',
                'dropped',
                'plan_to_read',
                'total',
                'scores' => [
                    1 => [
                        'votes',
                        'percentage'
                    ]
                ]
            ]);
    }

    public function testForum()
    {
        $this->get('/v3/manga/1/forum')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'topics' => [
                    [
                        'topic_id',
                        'url',
                        'title',
                        'date_posted',
                        'author_name',
                        'author_url',
                        'replies',
                        'last_post' => [
                            'url',
                            'author_name',
                            'author_url',
                            'date_posted'
                        ]
                    ]
                ]
            ]);
    }

    public function testMoreInfo()
    {
        $this->get('/v3/manga/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'moreinfo'
            ]);
    }

    public function testReviews()
    {
        $this->get('/v3/manga/1/reviews')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reviews' => [
                    [
                        'mal_id',
                        'url',
                        'helpful_count',
                        'date',
                        'reviewer' => [
                            'url',
                            'image_url',
                            'username',
                            'chapters_read',
                            'scores' => [
                                'overall',
                                'story',
                                'art',
                                'character',
                                'enjoyment'
                            ],
                        ],
                        'content'
                    ]
                ]
            ]);

        $this->get('/v3/manga/1/reviews/100')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reviews' => []
            ]);
    }

    public function testRecommendations()
    {
        $this->get('/v3/manga/1/recommendations')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'recommendations' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'recommendation_url',
                        'title',
                        'recommendation_count'
                    ]
                ]
            ]);
    }

    public function testUserUpdates()
    {
        $this->get('/v3/manga/1/userupdates')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'users' => [
                    [
                        'username',
                        'url',
                        'image_url',
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

        $this->get('/v3/manga/1/userupdates/1000')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->get('/v3/manga/1000000')
            ->seeStatusCode(404);
    }
}
