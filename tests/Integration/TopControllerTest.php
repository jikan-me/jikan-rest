<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Anime;
use App\Character;
use App\Manga;
use App\Person;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Jikan\Exception\BadResponseException;
use Jikan\Exception\ParserException;
use Jikan\Model\Anime\AnimeReview;
use Jikan\Model\Anime\AnimeReviewScores;
use Jikan\Model\Manga\MangaReview;
use Jikan\Model\Manga\MangaReviewScores;
use Jikan\Model\Resource\UserImageResource\UserImageResource;
use Jikan\Model\Reviews\Reactions;
use Jikan\Model\Reviews\Reviewer;
use Jikan\Model\Reviews\Reviews;
use Jikan\MyAnimeList\MalClient;
use Jikan\Parser\Reviews\AnimeReviewParser;
use Jikan\Parser\Reviews\MangaReviewParser;
use Jikan\Parser\Reviews\ReviewsParser;
use Tests\TestCase;

class TopControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function topReviewTypeParametersProvider(): array
    {
        return [
            "empty query string" => [[]],
            "query string = `?type=anime`" => [["type" => "anime"]],
            "query string = `?type=manga`" => [["type" => "manga"]],
        ];
    }

    public function testTopAnime()
    {
        Anime::factory(3)->state(new Sequence(
            ["rank" => 54],
            ["rank" => 22],
            ["rank" => 12]
        ))->create();
        $this->get('/v4/top/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
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
                    ]
                ]
            ]);
    }

    public function testTopManga()
    {
        Manga::factory(3)->state(new Sequence(
            ["rank" => 54],
            ["rank" => 22],
            ["rank" => 12]
        ))->create();
        $this->get('/v4/top/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
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
                    ]
                ]
            ]);
    }

    public function testTopPeople()
    {
        Person::factory(3)->state(new Sequence(
            ["member_favorites" => 524],
            ["member_favorites" => 323],
            ["member_favorites" => 224],
        ))->create();
        $this->get('/v4/top/people')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'mal_id',
                        'url',
                        'website_url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'name',
                        'given_name',
                        'family_name',
                        'alternate_names',
                        'birthday',
                        'favorites',
                        'about',
                    ]
                ]
            ]);
    }

    public function testTopCharacters()
    {
        Character::factory(3)->state(new Sequence(
            ["member_favorites" => 524],
            ["member_favorites" => 323],
            ["member_favorites" => 224],
        ))->create();
        $this->get('/v4/top/characters')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
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
                        'nicknames',
                        'favorites',
                        'about',
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->get('/v4/top/anime/999')
            ->seeStatusCode(404);
    }

    /**
     * @dataProvider topReviewTypeParametersProvider
     * @param $params
     * @return void
     * @throws BadResponseException
     * @throws ParserException
     */
    public function testTopReviews($params)
    {
        $jikanParser = \Mockery::mock(MalClient::class)->makePartial();

        $reviewsParser = \Mockery::mock(ReviewsParser::class)->makePartial();
        $reviewsParser->allows()->getReviews()->andReturn([]);
        $reviewsParser->allows()->hasNextPage()->andReturn(false);
        $reviewsFacade = Reviews::fromParser($reviewsParser);

        /** @noinspection PhpParamsInspection */
        $jikanParser->allows()->getReviews(\Mockery::any())->andReturn($reviewsFacade);
        $this->app->instance('JikanParser', $jikanParser);
        $this->getJsonResponse($params,"/v4/top/reviews");
        $this->seeStatusCode(200);
    }
}
