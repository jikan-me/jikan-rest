<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Integration;
use App\Person;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testMain()
    {
        Person::factory()->createOne([
            "mal_id" => 1
        ]);
        $this->get('/v4/people/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
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
            ]]);
    }


    public function testAnimeography()
    {
        Person::factory()->createOne([
            "mal_id" => 1,
            "anime_staff_positions" => [
                [
                    'position' => 'Theme Song Performance',
                    'anime' => [
                        'mal_id' => 3080,
                        'url' => 'https://myanimelist.net/anime/3080/Anime_Tenchou',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635t.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635l.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635t.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635l.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                            ],
                        ],
                        'title' => 'Anime Tenchou',
                    ],
                ]
            ]
        ]);
        $this->get('/v4/people/1/anime')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'position',
                    'anime' => [
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
                ]
            ]]);
    }

    public function testMangaography()
    {
        Person::factory()->createOne([
            "mal_id" => 1,
            "published_manga" => [
                [
                    'position' => 'Art',
                    'manga' => [
                        'mal_id' => 3080,
                        'url' => 'https://myanimelist.net/manga/3080/x',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635t.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635l.jpg?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635t.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/9/4635l.webp?s=ad3dd02ed42bccbc84e0bde5c9e4ac7d',
                            ],
                        ],
                        'title' => 'Anime Tenchou',
                    ],
                ]
            ]
        ]);
        $this->get('/v4/people/1/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'position',
                    'manga' => [
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
                ]
            ]]);
    }

    public function testVoices()
    {
        Person::factory()->createOne([
            "mal_id" => 1,
            "voice_acting_roles" => [
                [
                    'role' => 'Main',
                    'anime' => [
                        'mal_id' => 53127,
                        'url' => 'https://myanimelist.net/anime/53127/Fate_strange_Fake__Whispers_of_Dawn',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320.jpg?s=6b3f95b29d8156f15851f720f06c42ca',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320t.jpg?s=6b3f95b29d8156f15851f720f06c42ca',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320l.jpg?s=6b3f95b29d8156f15851f720f06c42ca',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320.webp?s=6b3f95b29d8156f15851f720f06c42ca',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320t.webp?s=6b3f95b29d8156f15851f720f06c42ca',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/1502/128320l.webp?s=6b3f95b29d8156f15851f720f06c42ca',
                            ],
                        ],
                        'title' => 'Fate/strange Fake: Whispers of Dawn',
                    ],
                    'character' => [
                        'mal_id' => 2514,
                        'url' => 'https://myanimelist.net/character/2514/Gilgamesh',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/r/84x124/images/characters/12/338672.jpg?s=45f9ea76970db587f1ab490b1a3573a1',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/r/84x124/images/characters/12/338672.webp?s=45f9ea76970db587f1ab490b1a3573a1',
                                'small_image_url' => 'https://cdn.myanimelist.net/r/84x124/images/characters/12/338672t.webp?s=45f9ea76970db587f1ab490b1a3573a1',
                            ],
                        ],
                        'name' => 'Gilgamesh',
                    ],
                ]
            ]
        ]);
        $this->get('/v4/people/1/voices')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'role',
                    'anime' => [
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
                    'character' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                            'webp' => [
                                'image_url',
                                'small_image_url',
                            ],
                        ],
                        'name'
                    ]
                ]
            ]]);
    }

    public function testPictures()
    {
        $document = $this->dummyScraperResultDocument('/v4/people/1/pictures', 'people', [
            'pictures' => [
                [
                    'jpg' => [
                        'image_url' => 'https://cdn.myanimelist.net/images/voiceactors/10/34138.jpg',
                    ],
                ]
            ]
        ]);
        DB::table("people_pictures")->insert($document);
        $this->get('/v4/people/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'jpg' => [
                            'image_url'
                        ]
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/people/1000000')
            ->seeStatusCode(404);
    }
}
