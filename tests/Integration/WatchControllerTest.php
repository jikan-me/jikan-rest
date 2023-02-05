<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WatchControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testWatchEpisodes()
    {
        $document = $this->dummyResultsDocument('/v4/watch/episodes', 'watch', [
            [
                'entry' => [
                    'mal_id' => 21,
                    'url' => 'https://myanimelist.net/anime/21/One_Piece',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245.jpg',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245t.jpg',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245.webp',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245t.webp',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245l.webp',
                        ],
                    ],
                    'title' => 'One Piece',
                ],
                'episodes' => [
                    [
                        'mal_id' => 1022,
                        'url' => 'https://myanimelist.net/anime/21/One_Piece/episode/1022',
                        'title' => 'Episode 1022',
                        'premium' => false,
                    ],
                    [
                        'mal_id' => 1021,
                        'url' => 'https://myanimelist.net/anime/21/One_Piece/episode/1021',
                        'title' => 'Episode 1021',
                        'premium' => false,
                    ],
                ],
                'region_locked' => false,
            ]
        ]);
        DB::table("watch")->insert($document);
        $this->get('/v4/watch/episodes')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
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
                        'episodes' => [
                            [
                                'mal_id',
                                'url',
                                'title',
                                'premium'
                            ]
                        ],
                    ]
                ]
            ]);
    }

    public function testWatchEpisodesPopular()
    {
        $document = $this->dummyResultsDocument('/v4/watch/episodes/popular', 'watch', [
            [
                'entry' => [
                    'mal_id' => 21,
                    'url' => 'https://myanimelist.net/anime/21/One_Piece',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245.jpg',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245t.jpg',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245.webp',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245t.webp',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/6/73245l.webp',
                        ],
                    ],
                    'title' => 'One Piece',
                ],
                'episodes' => [
                    [
                        'mal_id' => 1022,
                        'url' => 'https://myanimelist.net/anime/21/One_Piece/episode/1022',
                        'title' => 'Episode 1022',
                        'premium' => false,
                    ],
                    [
                        'mal_id' => 1021,
                        'url' => 'https://myanimelist.net/anime/21/One_Piece/episode/1021',
                        'title' => 'Episode 1021',
                        'premium' => false,
                    ],
                ],
                'region_locked' => false,
            ]
        ]);
        DB::table("watch")->insert($document);

        $this->get('/v4/watch/episodes/popular')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
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
                        'episodes' => [
                            [
                                'mal_id',
                                'url',
                                'title',
                                'premium'
                            ]
                        ],
                    ]
                ]
            ]);
    }

    public function testWatchPromos()
    {
        $document = $this->dummyResultsDocument('/v4/watch/promos', 'watch', [
            [
                'title' => 'Character PV',
                'entry' => [
                    'mal_id' => 51019,
                    'url' => 'https:\\/\\/myanimelist.net\\/anime\\/51019\\/Kimetsu_no_Yaiba__Katanakaji_no_Sato-hen',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027.jpg',
                            'small_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027t.jpg',
                            'large_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027.webp',
                            'small_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027t.webp',
                            'large_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027l.webp',
                        ],
                    ],
                    'title' => 'Kimetsu no Yaiba: Katanakaji no Sato-hen',
                ],
                'trailer' => [
                    'youtube_id' => 't0d7_6WCls8',
                    'url' => 'https:\\/\\/www.youtube.com\\/watch?v=t0d7_6WCls8',
                    'embed_url' => 'https:\\/\\/www.youtube.com\\/embed\\/t0d7_6WCls8?enablejsapi=1&wmode=opaque&autoplay=1',
                    'images' => [
                        'image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/default.jpg',
                        'small_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/sddefault.jpg',
                        'medium_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/mqdefault.jpg',
                        'large_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/hqdefault.jpg',
                        'maximum_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/maxresdefault.jpg',
                    ],
                ],
            ]
        ]);
        DB::table("watch")->insert($document);

        $this->get('/v4/watch/promos')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'title',
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
                ]
            ]);
    }

    public function testWatchPopularPromos()
    {
        $document = $this->dummyResultsDocument('/v4/watch/promos/popular', 'watch', [
            [
                'title' => 'Character PV',
                'entry' => [
                    'mal_id' => 51019,
                    'url' => 'https:\\/\\/myanimelist.net\\/anime\\/51019\\/Kimetsu_no_Yaiba__Katanakaji_no_Sato-hen',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027.jpg',
                            'small_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027t.jpg',
                            'large_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027.webp',
                            'small_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027t.webp',
                            'large_image_url' => 'https:\\/\\/cdn.myanimelist.net\\/images\\/anime\\/1499\\/121027l.webp',
                        ],
                    ],
                    'title' => 'Kimetsu no Yaiba: Katanakaji no Sato-hen',
                ],
                'trailer' => [
                    'youtube_id' => 't0d7_6WCls8',
                    'url' => 'https:\\/\\/www.youtube.com\\/watch?v=t0d7_6WCls8',
                    'embed_url' => 'https:\\/\\/www.youtube.com\\/embed\\/t0d7_6WCls8?enablejsapi=1&wmode=opaque&autoplay=1',
                    'images' => [
                        'image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/default.jpg',
                        'small_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/sddefault.jpg',
                        'medium_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/mqdefault.jpg',
                        'large_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/hqdefault.jpg',
                        'maximum_image_url' => 'https:\\/\\/img.youtube.com\\/vi\\/t0d7_6WCls8\\/maxresdefault.jpg',
                    ],
                ],
            ]
        ]);
        DB::table("watch")->insert($document);

        $this->get('/v4/watch/promos/popular')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                ],
                'data' => [
                    [
                        'title',
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
                ]
            ]);
    }

}
