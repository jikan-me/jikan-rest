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

    private function givenDummyCharactersStaffData($uri)
    {
        DB::table("anime_characters_staff")->insert([
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => "request:anime:" . sha1($uri),
            "characters" => [
                [
                    "character" => [
                        "mal_id" => 3,
                        "url" => "https://myanimelist.net/character/3/Jet_Black",
                        "images" => [
                            "jpg" => [
                                "image_url" => "https://cdn.myanimelist.net/images/characters/11/253723.jpg?s=6c8a19a79a88c46ae15f30e3ef5fd839",
                                "small_image_url" => "https://cdn.myanimelist.net/images/characters/11/253723t.jpg?s=6c8a19a79a88c46ae15f30e3ef5fd839"
                            ],
                            "webp" => [
                                "image_url" => "https://cdn.myanimelist.net/images/characters/11/253723.webp?s=6c8a19a79a88c46ae15f30e3ef5fd839",
                                "small_image_url" => "https://cdn.myanimelist.net/images/characters/11/253723t.webp?s=6c8a19a79a88c46ae15f30e3ef5fd839"
                            ]
                        ],
                        "name" => "Black, Jet"
                    ],
                    "role" => "Main",
                    "favorites" => 1,
                    "voice_actors" => [
                        [
                            "person" => [
                                "mal_id" => 357,
                                "url" => "https://myanimelist.net/people/357/Unshou_Ishizuk",
                                "images" => [
                                    "jpg" => [
                                        "image_url" => "https://cdn.myanimelist.net/images/voiceactors/2/17135.jpg?s=5925123b8a7cf9b51a445c225442f0ef"
                                    ]
                                ],
                                "name" => "Ishizuka, Unshou"
                            ],
                            "language" => "Japanese"
                        ]
                    ]
                ]
            ],
            "staff" => [
                [
                    "person" => [
                        "mal_id" => 40009,
                        "url" => "https://myanimelist.net/people/40009/Yutaka_Maseba",
                        "images" => [
                            "jpg" => [
                                "image_url" => "https://cdn.myanimelist.net/images/voiceactors/3/40216.jpg?s=d9fb7a625868ec7d9cd3804fa0da3fd6"
                            ]
                        ],
                        "name" => "Maseba, Yutaka"
                    ],
                    "positions" => [
                        "Producer"
                    ]
                ]
            ]
        ]);
    }

    public function testMain()
    {
        Anime::factory(1)->create([
            "mal_id" => 1
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
        $this->givenDummyCharactersStaffData("/v4/anime/1/characters");

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
        $this->givenDummyCharactersStaffData('/v4/anime/1/staff');
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
}
