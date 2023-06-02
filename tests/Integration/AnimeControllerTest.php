<?php
/** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;

use App\Anime;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Tests\TestCase;

class AnimeControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->searchIndexModelCleanupList = ["App\\Anime"];
    }

    public function testMain()
    {
        Anime::factory(1)->createOne([
            "mal_id" => 1,
            "studios" => [
                [
                    'mal_id' => 18,
                    'type' => 'anime',
                    'name' => 'Toei Animation',
                    'url' => 'https://myanimelist.net/anime/producer/18/Toei_Animation',
                ]
            ],
            "producers" => [
                [
                    'mal_id' => 16,
                    'type' => 'anime',
                    'name' => 'TV Tokyo',
                    'url' => 'https://myanimelist.net/anime/producer/16/TV_Tokyo',
                ]
            ],
            "licensors" => [
                [
                    'mal_id' => 102,
                    'type' => 'anime',
                    'name' => 'Funimation',
                    'url' => 'https://myanimelist.net/anime/producer/102/Funimation',
                ]
            ]
        ]);
        $this->getJson('/v4/anime/1')
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
        // let's avoid sending request to MAL in tests
        $this->givenDummyCharactersStaffData("/v4/anime/1/characters", "anime");

        $this->getJson('/v4/anime/1/characters')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data' => [
                [
                    'character' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                                'small_image_url',
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
        $this->givenDummyCharactersStaffData('/v4/anime/1/staff', "anime");
        $this->getJson('/v4/anime/1/staff')
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

    private function dummyEpisode(): array
    {
        return [
            "mal_id" => 301,
            "url" => "https://myanimelist.net/anime/516/Keroro_Gunsou/episode/301",
            "title" => "Tamama, Exiled from the Nishizawa House, Sir? / Momoka: A Chocolate ",
            "title_japanese" => null,
            "title_romanji" => null,
            "aired" => null,
            "filler" => false,
            "recap" => false,
            "score" => 4.5,
            "forum_url" => null
        ];
    }

    public function testEpisodesOne()
    {
        DB::table("anime_episodes")->insert(
            $this->dummyResultsDocument("/v4/anime/1/episodes", "anime", [
                $this->dummyEpisode()
            ])
        );
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
    }

    public function testEpisodesTwo()
    {
        DB::table("anime_episodes")->insert(
            $this->dummyResultsDocument(
                "/v4/anime/21/episodes?page=2",
                "anime",
                array_fill(0, 24, $this->dummyEpisode()),
                true,
                2
            )
        );
        $this->get('/v4/anime/21/episodes?page=2')
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
    }

    public function testEpisode()
    {
        $document = $this->dummyScraperResultDocument('/v4/anime/21/episodes/1', "anime", [
            'mal_id' => 1,
            'url' => 'https://myanimelist.net/anime/21/One_Piece/episode/1',
            'title' => 'I\'m Luffy! The Man Who\'s Gonna Be King of the Pirates!',
            'title_japanese' => '俺はルフィ!海賊王になる男だ!',
            'title_romanji' => 'Ore wa Luffy! Kaizoku Ou ni Naru Otoko Da!',
            'duration' => 1475,
            'aired' => '1999-10-20T00:00:00+09:00',
            'filler' => false,
            'recap' => false,
            'synopsis' => 'The series begins with an attack'
        ]);
        DB::table("anime_episode")->insert($document);
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
        $document = $this->dummyResultsDocument('/v4/anime/1/news', 'anime', [[
            'mal_id' => 60609964,
            'url' => 'https://myanimelist.net/news/60609964',
            'title' => 'North American Anime & Manga Releases for September',
            'date' => '2020-08-31T14:34:00+00:00',
            'author_username' => 'ImperfectBlue',
            'author_url' => 'https://myanimelist.net/profile/ImperfectBlue',
            'forum_url' => 'https://myanimelist.net/forum/?topicid=1862079',
            'images' => [
                'jpg' => [
                    'image_url' => 'https://cdn.myanimelist.net/s/common/uploaded_files/1598909553-a6f9acc1b6c36cd7b792e5bd67321c13.png?s=3b52b4fe7a2670d33b32d8397d2776bb',
                ],
            ],
            'comments' => 0,
            'excerpt' => 'Here are the North American anime & manga releases for September Week 1: September 1 - 7 Anime Releases Africa no Salaryman (TV) (Africa Salaryman) Complete Coll...',
        ]]);
        DB::table("anime_news")->insert($document);
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
        $document = $this->dummyScraperResultDocument(
            '/v4/anime/1/pictures',
            'anime',
            [[
                'jpg' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791.jpg',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791t.jpg',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791l.jpg',
                ],
                'webp' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791.webp',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791t.webp',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/7/3791l.webp',
                ],
            ]],
            'pictures'
        );
        DB::table("anime_pictures")->insert($document);
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
        $document = $this->dummyScraperResultDocument(
            '/v4/anime/1/videos',
            'anime',
            [
                'promo' => [
                    [
                        'title' => 'PV Blu-ray Box version',
                        'trailer' => [
                            'youtube_id' => 'qig4KOK2R2g',
                            'url' => 'https://www.youtube.com/watch?v=qig4KOK2R2g',
                            'embed_url' => 'https://www.youtube.com/embed/qig4KOK2R2g?enablejsapi=1&wmode=opaque&autoplay=1',
                            'images' => [
                                'image_url' => 'https://img.youtube.com/vi/qig4KOK2R2g/default.jpg',
                                'small_image_url' => 'https://img.youtube.com/vi/qig4KOK2R2g/sddefault.jpg',
                                'medium_image_url' => 'https://img.youtube.com/vi/qig4KOK2R2g/mqdefault.jpg',
                                'large_image_url' => 'https://img.youtube.com/vi/qig4KOK2R2g/hqdefault.jpg',
                                'maximum_image_url' => 'https://img.youtube.com/vi/qig4KOK2R2g/maxresdefault.jpg',
                            ],
                        ],
                    ]
                ],
                'episodes' => [
                    [
                        'mal_id' => 26,
                        'title' => 'The Real Folk Blues (part 2)',
                        'episode' => 'Episode 26',
                        'url' => 'https://myanimelist.net/anime/1/Cowboy_Bebop/episode/26',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://img1.ak.crunchyroll.com/i/spire1-tmb/191b230426f0b0e6568b4ca6edab47321473136587_large.jpg',
                            ],
                        ],
                    ]
                ],
                'music_videos' => [
                    [
                        'title' => 'OP 1 (Artist ver.)',
                        'video' => [
                            'youtube_id' => 'wbaILDE7Dco',
                            'url' => 'https://www.youtube.com/watch?v=wbaILDE7Dco',
                            'embed_url' => 'https://www.youtube.com/embed/wbaILDE7Dco?enablejsapi=1&wmode=opaque&autoplay=1',
                            'images' => [
                                'image_url' => 'https://img.youtube.com/vi/wbaILDE7Dco/default.jpg',
                                'small_image_url' => 'https://img.youtube.com/vi/wbaILDE7Dco/sddefault.jpg',
                                'medium_image_url' => 'https://img.youtube.com/vi/wbaILDE7Dco/mqdefault.jpg',
                                'large_image_url' => 'https://img.youtube.com/vi/wbaILDE7Dco/hqdefault.jpg',
                                'maximum_image_url' => 'https://img.youtube.com/vi/wbaILDE7Dco/maxresdefault.jpg',
                            ],
                        ],
                        'meta' => [
                            'title' => '"heavenly blue"',
                            'author' => 'Kalafina',
                        ],
                    ]
                ]
            ]
        );
        DB::table('anime_videos')->insert($document);
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
                ],
                'music_videos' => [
                    [
                        'title',
                        'video' => [
                            'youtube_id',
                            'url',
                            'embed_url',
                            'images' => [
                                'image_url',
                                'small_image_url',
                                'medium_image_url',
                                'large_image_url',
                                'maximum_image_url',
                            ],
                        ],
                        'meta' => [
                            'title',
                            'author',
                        ],
                    ]
                ]
            ]]);
    }

    public function testStats()
    {
        $document = $this->dummyScraperResultDocument('/v4/anime/21/statistics', "anime",
            [
                'watching' => 1293641,
                'completed' => 37,
                'on_hold' => 239382,
                'dropped' => 164598,
                'plan_to_watch' => 195375,
                'total' => 1893033,
                'scores' => [
                    [
                        'score' => 1,
                        'votes' => 9470,
                        'percentage' => 0.9,
                    ],
                    [
                        'score' => 2,
                        'votes' => 3363,
                        'percentage' => 0.3,
                    ],
                ],
            ]
        );
        DB::table("anime_stats")->insert($document);
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
        $document = $this->dummyScraperResultDocument('/v4/anime/1/forum', "anime", [
            [
                'mal_id' => 2022869,
                'url' => 'https://myanimelist.net/forum/?topicid=2022869',
                'title' => 'What was the reception like when this first came out?',
                'date' => '2022-06-15T00:00:00+00:00',
                'author_username' => 'NextUniverse',
                'author_url' => 'https://myanimelist.net/profile/NextUniverse',
                'comments' => 7,
                'last_comment' => [
                    'url' => 'https://myanimelist.net/forum/?topicid=2022869&goto=lastpost',
                    'author_username' => 'Bacon_and_Eggs',
                    'author_url' => 'https://myanimelist.net/profile/Bacon_and_Eggs',
                    'date' => '2022-06-19T06:26:00+00:00',
                ],
            ]
        ], "topics");
        DB::table("anime_forum")->insert($document);
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
        $document = $this->dummyScraperResultDocument('/v4/anime/1/moreinfo', "anime", ["moreinfo" => "asd"]);
        DB::table("anime_moreinfo")->insert($document);
        $this->get('/v4/anime/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'moreinfo'
            ]]);
    }

    public function testReviewsOne()
    {
        $document = $this->dummyResultsDocument('/v4/anime/1/reviews', "anime", [
            [
                'mal_id' => 7406,
                'url' => 'https://myanimelist.net/reviews.php?id=7406',
                'type' => 'anime',
                'reactions' => [
                    'overall' => 2112,
                    'nice' => 2105,
                    'love_it' => 3,
                    'funny' => 1,
                    'confusing' => 0,
                    'informative' => 2,
                    'well_written' => 1,
                    'creative' => 0,
                ],
                'date' => '2008-08-24T05:46:00+00:00',
                'review' => 'People who know me dd',
                'score' => 10,
                'tags' => [
                    0 => 'Recommended',
                ],
                'is_spoiler' => false,
                'is_preliminary' => false,
                'episodes_watched' => NULL,
                'user' => [
                    'url' => 'https://myanimelist.net/profile/TheLlama',
                    'username' => 'TheLlama',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/11081.jpg?t=1666216200',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/11081.webp?t=1666216200',
                        ],
                    ],
                ],
            ]
        ]);
        DB::table("anime_reviews")->insert($document);
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
    }

    public function testReviewsTwo()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/anime/1/reviews?page=100')
            ->seeStatusCode(404)
            ->seeJsonStructure([
                'status',
                'type',
                'message',
                'error'
            ]);
    }

    public function testRecommendations()
    {
        $document = $this->dummyScraperResultDocument('/v4/anime/1/recommendations', 'anime', [
            'recommendations' => [
                [
                    'entry' => [
                        'mal_id' => 205,
                        'url' => 'https://myanimelist.net/anime/205/Samurai_Champloo',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599.jpg?s=690d61d9517bcc79a007c21f1e9b58e8',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599t.jpg?s=690d61d9517bcc79a007c21f1e9b58e8',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599l.jpg?s=690d61d9517bcc79a007c21f1e9b58e8',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599.webp?s=690d61d9517bcc79a007c21f1e9b58e8',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599t.webp?s=690d61d9517bcc79a007c21f1e9b58e8',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1375/121599l.webp?s=690d61d9517bcc79a007c21f1e9b58e8',
                            ],
                        ],
                        'title' => 'Samurai Champloo',
                    ],
                    'url' => 'https://myanimelist.net/recommendations/anime/1-205',
                    'votes' => 118,
                ]
            ]
        ]);
        DB::table('anime_recommendations')->insert($document);
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
        $document = $this->dummyResultsDocument('/v4/anime/1/userupdates', 'anime', [
            [
                'user' => [
                    'username' => 'Mar-E',
                    'url' => 'https://myanimelist.net/profile/Mar-E',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/12234611.jpg?t=1675204800',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/userimages/12234611.webp?t=1675204800',
                        ],
                    ],
                ],
                'score' => NULL,
                'status' => 'Watching',
                'episodes_seen' => 16,
                'episodes_total' => 26,
                'date' => '2023-01-31T22:34:00+00:00',
            ]
        ]);
        DB::table("anime_userupdates")->insert($document);
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
    }

    public function testAnimeUserUpdatesNotFound()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/anime/1/userupdates?page=200')
            ->seeStatusCode(404);
    }

    public function testAnimeRelations()
    {
        Anime::factory()->createOne([
            "mal_id" => 1,
            "related" => [
                [
                    'relation' => 'Adaptation',
                    'entry' => [
                        0 => [
                            'mal_id' => 173,
                            'type' => 'manga',
                            'name' => 'Cowboy Bebop',
                            'url' => 'https://myanimelist.net/manga/173/Cowboy_Bebop',
                        ],
                        1 => [
                            'mal_id' => 174,
                            'type' => 'manga',
                            'name' => 'Shooting Star Bebop: Cowboy Bebop',
                            'url' => 'https://myanimelist.net/manga/174/Shooting_Star_Bebop__Cowboy_Bebop',
                        ],
                    ],
                ]
            ]
        ]);
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
        Anime::factory()->createOne([
            "mal_id" => 1,
            "themes" => [
                [
                    'mal_id' => 50,
                    'type' => 'anime',
                    'name' => 'Adult Cast',
                    'url' => 'https://myanimelist.net/anime/genre/50/Adult_Cast',
                ]
            ]
        ]);
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
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/anime/2')
            ->seeStatusCode(404);
    }
}
