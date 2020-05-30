<?php

class MangaControllerV4Test extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/manga/1')
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
                    [
                        'relation',
                        'items' => [
                            'mal_id',
                            'type',
                            'name',
                            'url'
                        ]
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
        $this->get('/v4/manga/1/characters')
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
        $this->get('/v4/manga/1/news')
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
                'images' => [
                    [
                        'large_image_url',
                        'small_image_url',
                    ]
                ]
            ]);
    }

    public function testStats()
    {
        $this->get('/v4/manga/1/statistics')
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
        $this->get('/v4/manga/1/forum')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'topics' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'date',
                        'author_name',
                        'author_url',
                        'replies',
                        'last_comment' => [
                            'url',
                            'author_name',
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
            ->seeJsonStructure([
                'moreinfo'
            ]);
    }

    public function testReviews()
    {
        $this->get('/v4/manga/1/reviews')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reviews' => [
                    [
                        'mal_id',
                        'url',
                        'helpful_count',
                        'date',
                        'scores' => [
                            'overall',
                            'story',
                            'animation',
                            'sound',
                            'character',
                            'enjoyment'
                        ],
                        'content',
                        'reviewer' => [
                            'url',
                            'image_url',
                            'username',
                            'episodes_seen'
                        ]
                    ]
                ]
            ]);

        $this->get('/v4/manga/1/reviews/100')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reviews' => []
            ]);
    }

    public function testRecommendations()
    {
        $this->get('/v4/manga/1/recommendations')
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
        $this->get('/v4/manga/1/userupdates')
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

        $this->get('/v4/manga/1/userupdates/1000')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->get('/v4/manga/1000000')
            ->seeStatusCode(404);
    }
}
