<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use Tests\TestCase;

class AnimeControllerTest extends TestCase
{
    public function testMain()
    {
        $this->get('/v4/anime/1')
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
                'trailer' => [
                    'youtube_id',
                    'url',
                    'embed_url',
                    'images' => [
                        'image_url',
                        'small_image_url',
                        'medium_image_url',
                        'large_image_url',
                        'maximum_image_url',
                    ]
                ],
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
                'season',
                'year',
                'broadcast' => [
                    'day',
                    'time',
                    'timezone',
                    'string'
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
            ]]);
    }

    public function testCharacters()
    {
        $this->get('/v4/anime/1/characters')
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
                    'voice_actors' => [
                        [
                            'person' => [
                                'mal_id',
                                'images' => [
                                    'jpg' => [
                                        'image_url',
                                    ],
                                ],
                                'name'
                            ],
                            'language'
                        ]
                    ]
                ]
            ]]);
    }

    public function testStaff()
    {
        $this->get('/v4/anime/1/staff')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'person' => [
                        'mal_id',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'name'
                    ],
                    'positions'
                ]
            ]]);
    }

    public function testEpisodes()
    {
        $this->get('/v4/anime/1/episodes')
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
                        'title_japanese',
                        'title_romanji',
                        'aired',
                        'score',
                        'filler',
                        'recap',
                        'forum_url'
                    ]
                ]
            ]);

        $this->get('/v4/anime/21/episodes?page=2')
            ->seeStatusCode(200)
            ->seeJson([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'title',
                        'title_japanese',
                        'title_romanji',
                        'aired',
                        'score',
                        'filler',
                        'recap',
                        'forum_url'
                    ]
                ]
            ]);
    }

    public function testEpisode()
    {
        $this->get('/v4/anime/21/episodes/1')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'mal_id',
                    'url',
                    'title',
                    'title_japanese',
                    'title_romanji',
                    'duration',
                    'aired',
                    'aired',
                    'filler',
                    'recap',
                    'synopsis',
                ]
            ]);
    }

    public function testNews()
    {
        $this->get('/v4/anime/1/news')
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
        $this->get('/v4/anime/1/pictures')
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
                        ]
                    ]
                ]
            ]);
    }

    public function testVideos()
    {
        $this->get('/v4/anime/1/videos')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'promo' => [
                    [
                        'title',
                        'trailer' => [
                            'youtube_id',
                            'url',
                            'embed_url',
                            'images' => [
                                'image_url',
                                'small_image_url',
                                'medium_image_url',
                                'large_image_url',
                                'maximum_image_url',
                            ]
                        ],
                    ]
                ],
                'episodes' => [
                    [
                        'mal_id',
                        'title',
                        'episode',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                    ]
                ]
            ]]);
    }

    public function testStats()
    {
        $this->get('/v4/anime/21/statistics')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'watching',
                'completed',
                'on_hold',
                'dropped',
                'plan_to_watch',
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
        $this->get('/v4/anime/1/forum')
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
        $this->get('/v4/anime/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'moreinfo'
            ]]);
    }

    public function testReviews()
    {
        $this->get('/v4/anime/1/reviews')
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

        $this->get('/v4/anime/1/reviews?page=100')
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
        $this->get('/v4/anime/1/recommendations')
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

    public function testAnimeUserUpdates()
    {
        $this->get('/v4/anime/1/userupdates')
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
                        'episodes_seen',
                        'episodes_total',
                        'date'
                    ]
                ]
            ]);

        $this->get('/v4/anime/1/userupdates?page=200')
            ->seeStatusCode(404);
    }

    public function testAnimeRelations()
    {
        $this->get('/v4/anime/1/relations')
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

    public function testAnimeThemes()
    {
        $this->get('/v4/anime/1/themes')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'openings',
                    'endings',
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v4/anime/2')
            ->seeStatusCode(404);
    }
}
