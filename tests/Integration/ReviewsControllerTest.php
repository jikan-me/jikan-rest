<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewsControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeReviews()
    {
        $document = $this->dummyResultsDocument('/v4/reviews/anime', 'reviews', [
            [
                'mal_id' => 448579,
                'url' => 'https://myanimelist.net/reviews.php?id=448579',
                'type' => 'anime',
                'reactions' => [
                    'overall' => 0,
                    'nice' => 0,
                    'love_it' => 0,
                    'funny' => 0,
                    'confusing' => 0,
                    'informative' => 0,
                    'well_written' => 0,
                    'creative' => 0,
                ],
                'votes' => 0,
                'score' => 4,
                'tags' => ['Recommended'],
                'is_spoiler' => false,
                'is_preliminary' => false,
                'date' => '2022-06-20T12:13:00+00:00',
                'review' => 'Its good and enjoyable until Kanade drama kick in and it goes downhill since then. The drama is irritating to watch...',
                'episodes_watched' => 12,
                'scores' => [
                    'overall' => 5,
                    'story' => 3,
                    'animation' => 7,
                    'sound' => 5,
                    'character' => 5,
                    'enjoyment' => 4,
                ],
                'entry' => [
                    'mal_id' => 43470,
                    'url' => 'https://myanimelist.net/anime/43470/Rikei_ga_Koi_ni_Ochita_no_de_Shoumei_shitemita_Heart',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948.jpg?s=22c36e71a40927d69da106c932bda628',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948t.jpg?s=22c36e71a40927d69da106c932bda628',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948l.jpg?s=22c36e71a40927d69da106c932bda628',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948.webp?s=22c36e71a40927d69da106c932bda628',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948t.webp?s=22c36e71a40927d69da106c932bda628',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948l.webp?s=22c36e71a40927d69da106c932bda628',
                        ],
                    ],
                    'title' => 'Rikei ga Koi ni Ochita no de Shoumei shitemita. Heart',
                ],
                'user' => [
                    'url' => 'https://myanimelist.net/profile/helmy47',
                    'username' => 'helmy47',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/9023550.jpg?t=1655753400',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/9023550.webp?t=1655753400',
                        ],
                    ],
                ],
            ]
        ]);
        DB::table("reviews")->insert($document);
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
        $document = $this->dummyResultsDocument('/v4/reviews/manga', 'reviews', [
            [
                'mal_id' => 448579,
                'url' => 'https://myanimelist.net/reviews.php?id=448579',
                'type' => 'manga',
                'reactions' => [
                    'overall' => 0,
                    'nice' => 0,
                    'love_it' => 0,
                    'funny' => 0,
                    'confusing' => 0,
                    'informative' => 0,
                    'well_written' => 0,
                    'creative' => 0,
                ],
                'votes' => 0,
                'date' => '2022-06-20T12:13:00+00:00',
                'review' => 'Its good and enjoyable until Kanade drama kick in and it goes downhill since then. The drama is irritating to watch...',
                'chapters_read' => 75,
                'score' => 5,
                'is_spoiler' => false,
                'is_preliminary' => false,
                'tags' => ['Recommended'],
                'entry' => [
                    'mal_id' => 43470,
                    'url' => 'https://myanimelist.net/anime/43470/Rikei_ga_Koi_ni_Ochita_no_de_Shoumei_shitemita_Heart',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948.jpg?s=22c36e71a40927d69da106c932bda628',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948t.jpg?s=22c36e71a40927d69da106c932bda628',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948l.jpg?s=22c36e71a40927d69da106c932bda628',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948.webp?s=22c36e71a40927d69da106c932bda628',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948t.webp?s=22c36e71a40927d69da106c932bda628',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1109/118948l.webp?s=22c36e71a40927d69da106c932bda628',
                        ],
                    ],
                    'title' => 'Rikei ga Koi ni Ochita no de Shoumei shitemita. Heart',
                ],
                'user' => [
                    'url' => 'https://myanimelist.net/profile/helmy47',
                    'username' => 'helmy47',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/9023550.jpg?t=1655753400',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/9023550.webp?t=1655753400',
                        ],
                    ],
                ],
            ]
        ]);
        DB::table("reviews")->insert($document);
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
