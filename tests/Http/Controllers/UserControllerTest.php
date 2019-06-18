<?php

class UserControllerTest extends TestCase
{
    public function testUserProfile()
    {
        $this->get('/v3/user/nekomata1037')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'username',
                'url',
                'image_url',
                'last_online',
                'gender',
                'birthday',
                'location',
                'joined',
                'anime_stats' => [
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
                'manga_stats' => [
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
                'favorites' => [
                    'anime' => [
                        [
                            'mal_id',
                            'url',
                            'image_url',
                            'name' // todo should be `title`
                        ]
                    ],
                    'manga' => [],
                    'characters' => [
                        [
                            'mal_id',
                            'url',
                            'image_url',
                            'name'
                        ]
                    ],
                    'people' => [
                        [
                            'mal_id',
                            'url',
                            'image_url',
                            'name'
                        ]
                    ],
                ],
                'about'
            ]);
    }

    public function testUserHistory()
    {
        $this->get('/v3/user/nekomata1037/history')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'history' => [
                    [
                        'meta' => [
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
    }

    public function testUserFriends()
    {
        $this->get('/v3/user/nekomata1037/friends')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'friends' => [
                    [
                        'url',
                        'username',
                        'image_url',
                        'last_online',
                        'friends_since'
                    ]
                ]
            ]);
    }

    public function testUserAnimeList()
    {
        $this->get('/v3/user/nekomata1037/animelist?order_by=last_updated&sort=descending')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'anime' => [
                    [
                        'mal_id',
                        'title',
                        'video_url',
                        'url',
                        'image_url',
                        'type',
                        'watching_status',
                        'score',
                        'watched_episodes',
                        'total_episodes',
                        'airing_status',
                        'season_name',
                        'season_year',
                        'has_episode_video',
                        'has_promo_video',
                        'has_video',
                        'is_rewatching',
                        'tags',
                        'rating',
                        'start_date',
                        'end_date',
                        'watch_start_date',
                        'watch_end_date',
                        'days',
                        'storage',
                        'priority',
                        'added_to_list',
                        'studios' => [
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
                    ]
                ]
            ]);
    }

    public function testUserMangaList()
    {
        $this->get('/v3/user/nekomata1037/mangalist?order_by=last_updated&sort=descending')
            ->seeStatusCode(400);
    }
}
