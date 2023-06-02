<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Profile;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jikan\Model\Recommendations\UserRecommendations;
use Jikan\Model\User\Friends;
use Jikan\Model\User\Reviews\UserReviews;
use Jikan\MyAnimeList\MalClient;
use Jikan\Request\User\UserRecommendationsRequest;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testUserProfile()
    {
        Profile::factory()->createOne([
            "username" => "nekomata1037"
        ]);
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
        Profile::factory()->createOne([
            "username" => "nekomata1037"
        ]);
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
        Profile::factory()->createOne([
            "username" => "nekomata1037"
        ]);
        $this->get('/v4/users/nekomata1037/about')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                'about',
            ]]);
    }

    public function testUserFavorites()
    {
        Profile::factory()->createOne([
            "username" => "nekomata1037"
        ]);
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



    public function testUserAnimeHistory()
    {
        $document = $this->dummyScraperResultDocument('/v4/users/purplepinapples/history/anime', 'users', [
            'history' => [
                [
                    'entry' => [
                        'mal_id' => $this->faker->numberBetween(2, 9999),
                        'type' => 'anime',
                        'name' => $this->faker->name(),
                        'url' => $this->faker->url()
                    ],
                    'increment' => 1,
                    'date' => Carbon::now()->toAtomString()
                ]
            ]
        ]);
        DB::table("users_history")->insert($document);
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
    }

    public function testUserMangaHistory()
    {
        $document = $this->dummyScraperResultDocument('/v4/users/nekomata1037/history/manga', 'users', [
            'history' => [
                [
                    'entry' => [
                        'mal_id' => $this->faker->numberBetween(2, 9999),
                        'type' => 'manga',
                        'name' => $this->faker->name(),
                        'url' => $this->faker->url()
                    ],
                    'increment' => 1,
                    'date' => Carbon::now()->toAtomString()
                ]
            ]
        ]);
        DB::table("users_history")->insert($document);
        $this->get('/v4/users/nekomata1037/history/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [

                ]
            ]);
    }

    public function testUserFriends()
    {
        $document = $this->dummyResultsDocument('/v4/users/nekomata1037/friends', 'users', [[
            'user' => [
                'username' => $this->faker->userName(),
                'url' => $this->faker->url(),
                "images" => [
                    "jpg" => [
                        "image_url" => $this->faker->url()
                    ],
                    "webp" => [
                        "image_url" => $this->faker->url()
                    ]
                ],
            ],
            'last_online' => Carbon::now()->toAtomString(),
            'friends_since' => Carbon::now()->toAtomString()
        ]]);
        DB::table("users_friends")->insert($document);
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
    }

    public function testInvalidUserFriendsRequest()
    {
        $jikanParser = \Mockery::mock(MalClient::class)->makePartial();
        /** @noinspection PhpParamsInspection */
        $jikanParser->allows()
            ->getUserFriends(\Mockery::any())
            ->andReturn(new Friends());
        $this->app->instance('JikanParser', $jikanParser);

        $this->get('/v4/users/nekomata1037/friends?page=200')
            ->seeStatusCode(200);
    }

    public function testUserRecommendations()
    {
        $document = $this->dummyResultsDocument('/v4/users/xinil/recommendations', 'users', [
            [
                'mal_id' => '263-1559',
                'entry' => [
                    [
                        'mal_id' => 263,
                        'url' => 'https://myanimelist.net/anime/263/Hajime_no_Ippo',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334.jpg?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334t.jpg?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334l.jpg?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334.webp?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334t.webp?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/86334l.webp?s=20b66bb8d0eb0af0a1810eb8717ec44f',
                            ],
                        ],
                        'title' => 'Hajime no Ippo',
                    ]
                ],
                'content' => 'Hajime no Ippo and Kenichi are very similar, mostly by looking at the main characters. Makunouchi is a weakling, Kenichi is a weakling. However, they both train hard and become stronger in order to achieve their goals. Makounouchi wants to know what it means to be "strong", and Kenichi wants to become stronger to protect his friends from harm.',
                'date' => '2008-01-20T00:00:00+00:00',
                'user' => [
                    'url' => 'https://myanimelist.net/profile/Xinil',
                    'username' => 'Xinil',
                ],
            ]
        ]);
        DB::table("users_recommendations")->insert($document);
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
    }

    public function testInvalidUserRecommendationsRequest()
    {
        // let's add a parser which returns nothing all the time for user recommendations request
        $jikanParser = \Mockery::mock(MalClient::class)->makePartial();
        /** @noinspection PhpParamsInspection */
        $jikanParser->allows()
            ->getUserRecommendations(\Mockery::type(UserRecommendationsRequest::class))
            ->andReturn(new UserRecommendations());
        $this->app->instance('JikanParser', $jikanParser);

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
        $document = $this->dummyResultsDocument('/v4/users/xinil/reviews', 'users', [[
            'mal_id' => 829,
            'url' => 'https://myanimelist.net/reviews.php?id=829',
            'type' => 'manga',
            'votes' => 78,
            'date' => '2007-09-09T16:43:00+00:00',
            'review' => 'Just an all around &quot;fun&quot;',
            'chapters_read' => 89,
            'is_spoiler' => false,
            'is_preliminary' => false,
            'tags' => [],
            'score' => 1,
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
            'scores' => [
                'overall' => 7,
                'story' => 8,
                'art' => 6,
                'character' => 7,
                'enjoyment' => 8,
            ],
            'entry' => [
                'mal_id' => 1190,
                'url' => 'https://myanimelist.net/manga/1190/Asatte_Dance',
                'images' => [
                    'jpg' => [
                        'image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582.jpg?s=3035988a19db4f7223de8b0168bdae30',
                        'small_image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582t.jpg?s=3035988a19db4f7223de8b0168bdae30',
                        'large_image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582l.jpg?s=3035988a19db4f7223de8b0168bdae30',
                    ],
                    'webp' => [
                        'image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582.webp?s=3035988a19db4f7223de8b0168bdae30',
                        'small_image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582t.webp?s=3035988a19db4f7223de8b0168bdae30',
                        'large_image_url' => 'https://cdn.myanimelist.net/images/manga/5/202582l.webp?s=3035988a19db4f7223de8b0168bdae30',
                    ],
                ],
                'title' => 'Asatte Dance',
            ],
        ]]);
        DB::table("users_reviews")->insert($document);
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
    }

    public function testInvalidUserReviewsRequest()
    {
        $jikanParser = \Mockery::mock(MalClient::class)->makePartial();
        /** @noinspection PhpParamsInspection */
        $jikanParser->allows()
            ->getUserReviews(\Mockery::any())
            ->andReturn(new UserReviews());
        $this->app->instance('JikanParser', $jikanParser);

        $this->get('/v4/users/xinil/reviews?page=200')
            ->seeStatusCode(200);
    }

    public function testUserClubs()
    {
        $document = $this->dummyResultsDocument('/v4/users/nekomata1037/clubs', 'users', [[
            'mal_id' => 1,
            'name' => 'a',
            'url' => $this->faker->url()
        ]]);
        DB::table('users_clubs')->insert($document);
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
