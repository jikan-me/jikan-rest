<?php

class AnimeControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v3/anime/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'mal_id',
                'url',
                'image_url',
                'trailer_url',
                'title',
                'title_english',
                'title_japanese',
                'title_synonyms',
                'type',
                'source',
                'episodes',
                'status',
                'airing',
                'aired' => [
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
                'duration',
                'rating',
                'score',
                'scored_by',
                'rank',
                'popularity',
                'members',
                'favorites',
                'synopsis',
                'background',
                'premiered',
                'broadcast',
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
                'producers' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url'
                    ]
                ],
                'licensors' => [
                    [
                        'mal_id',
                        'type',
                        'name',
                        'url'
                    ]
                ],
                'studios' => [
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
                ],
                'opening_themes',
                'ending_themes'
            ]);
    }

    public function testCharactersStaff()
    {
        $this->get('/v3/anime/1/characters_staff')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'characters' => [
                    [
                        'mal_id',
                        'url',
                        'image_url',
                        'name',
                        'voice_actors' => [
                            [
                                'mal_id',
                                'name',
                                'image_url',
                                'language'
                            ]
                        ]
                    ]
                ],
                'staff' => [
                    [
                        'mal_id',
                        'url',
                        'name',
                        'image_url',
                        'positions'
                    ]
                ],
            ]);
    }

    public function testEpisodes()
    {
        $this->get('/v3/anime/1/episodes')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'episodes_last_page',
                'episodes' => [
                    [
                        'episode_id',
                        'title',
                        'title_japanese',
                        'title_romanji',
                        'aired',
                        'filler',
                        'recap',
                        'video_url',
                        'forum_url'
                    ]
                ]
            ]);

        $this->get('/v3/anime/1/episodes/2')
            ->seeStatusCode(200)
            ->seeJson([
                'episodes' => []
            ]);
    }

    public function testNews()
    {
        $this->get('/v3/anime/1/news')
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
        $this->get('/v3/anime/1/pictures')
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

    public function testVideos()
    {
        $this->get('/v3/anime/1/videos')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'promo' => [
                    [
                        'title',
                        'image_url',
                        'video_url',
                    ]
                ],
                'episodes' => [
                    [
                        'title',
                        'episode',
                        'url',
                        'image_url',
                    ]
                ]
            ]);
    }

    public function testStats()
    {
        $this->get('/v3/anime/1/stats')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'watching',
                'completed',
                'on_hold',
                'dropped',
                'plan_to_watch',
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
        $this->get('/v3/anime/1/forum')
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
        $this->get('/v3/anime/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'moreinfo'
            ]);
    }

    public function testReviews()
    {
        $this->get('/v3/anime/1/reviews')
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
                            'episodes_seen',
                            'scores' => [
                                'overall',
                                'story',
                                'animation',
                                'sound',
                                'character',
                                'enjoyment'
                            ],
                        ],
                        'content'
                    ]
                ]
            ]);

        $this->get('/v3/anime/1/reviews/100')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'reviews' => []
            ]);
    }

    public function testRecommendations()
    {
        $this->get('/v3/anime/1/recommendations')
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
        $this->get('/v3/anime/1/userupdates')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'users' => [
                    [
                        'username',
                        'url',
                        'image_url',
                        'score',
                        'status',
                        'episodes_seen',
                        'episodes_total',
                        'date'
                    ]
                ]
            ]);

        $this->get('/v3/anime/1/userupdates/1000')
            ->seeStatusCode(404);
    }

    public function test404()
    {
        $this->get('/v3/anime/2')
            ->seeStatusCode(404);
    }
}
