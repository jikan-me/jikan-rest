<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Manga;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MangaControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testMain()
    {
        Manga::factory()->createOne([
            "mal_id" => 1
        ]);
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
        $this->givenDummyCharactersStaffData("/v4/manga/1/characters", "manga");

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
        $document = $this->dummyResultsDocument('/v4/manga/1/news', 'manga', [[
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
        DB::table("manga_news")->insert($document);
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
        $document = $this->dummyScraperResultDocument(
            '/v4/manga/1/pictures',
            'manga',
            [[
                'jpg' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791.jpg',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791t.jpg',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791l.jpg',
                ],
                'webp' => [
                    'image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791.webp',
                    'small_image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791t.webp',
                    'large_image_url' => 'https://cdn.myanimelist.net/images/manga/7/3791l.webp',
                ],
            ]],
            'pictures'
        );
        DB::table("manga_pictures")->insert($document);
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
        $document = $this->dummyScraperResultDocument('/v4/manga/1/statistics', "manga",
            [
                'reading' => 1293641,
                'completed' => 37,
                'on_hold' => 239382,
                'dropped' => 164598,
                'plan_to_read' => 195375,
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
        DB::table("manga_stats")->insert($document);
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
        $document = $this->dummyScraperResultDocument('/v4/manga/1/forum', "manga", [
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
        DB::table("manga_forum")->insert($document);
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
        $document = $this->dummyScraperResultDocument('/v4/manga/1/moreinfo', "manga", ["moreinfo" => "asd"]);
        DB::table("manga_moreinfo")->insert($document);
        $this->get('/v4/manga/1/moreinfo')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'moreinfo'
            ]]);
    }

    public function testReviews()
    {
        $document = $this->dummyResultsDocument('/v4/manga/1/reviews', "manga", [
            [
                'mal_id' => 7406,
                'url' => 'https://myanimelist.net/reviews.php?id=7406',
                'type' => 'manga',
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
                'chapters_read' => null,
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
        DB::table("manga_reviews")->insert($document);
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
    }

    public function testReviewsTwo()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/manga/1/reviews?page=100')
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
        $document = $this->dummyScraperResultDocument('/v4/manga/1/recommendations', 'manga', [
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
        DB::table('manga_recommendations')->insert($document);
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
        $document = $this->dummyResultsDocument('/v4/manga/1/userupdates', 'manga', [
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
                'volumes_read' => 16,
                'volumes_total' => 26,
                'chapters_read' => 22,
                'chapters_total' => 22,
                'date' => '2023-01-31T22:34:00+00:00',
            ]
        ]);
        DB::table("manga_userupdates")->insert($document);
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
    }

    public function testUserUpdatesNotFound()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/manga/1/userupdates?page=200')
            ->seeStatusCode(404);
    }

    public function testMangaRelations()
    {
        Manga::factory()->createOne([
            "mal_id" => 1,
            "related" => [
                [
                    'relation' => 'Other',
                    'entry' => [
                        [
                            'mal_id' => 793,
                            'type' => 'manga',
                            'name' => 'Wanted!',
                            'url' => 'https://myanimelist.net/manga/793/Wanted',
                        ],
                        [
                            'mal_id' => 25146,
                            'type' => 'manga',
                            'name' => 'One Piece x Toriko',
                            'url' => 'https://myanimelist.net/manga/25146/One_Piece_x_Toriko',
                        ],
                    ],
                ]
            ]
        ]);
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
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/manga/1000000')
            ->seeStatusCode(404);
    }
}
