<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Character;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;


class CharacterControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testMain()
    {
        Character::factory()->createOne([
            "mal_id" => 1
        ]);
        $this->get('/v4/characters/1')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
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
            ]]);
    }

    public function testAnimeography()
    {
        Character::factory()->createOne([
            "mal_id" => 1,
            "animeography" => [
                [
                    "role" => "Main",
                    "anime" => [
                        "mal_id" => 1,
                        "url" => "https://myanimelist.net/anime/1/Cowboy_Bebop",
                        "images" => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                            ],
                        ],
                        "title" => "Cowboy Bebop"
                    ]
                ]
            ]
        ]);
        $this->get('/v4/characters/1/anime')
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
                    ]
                ]
            ]]);
    }

    public function testMangaography()
    {
        Character::factory()->createOne([
            "mal_id" => 1,
            "mangaography" => [
                [
                    "role" => "Main",
                    "manga" => [
                        "mal_id" => 1,
                        "url" => "https://myanimelist.net/anime/1/Cowboy_Bebop",
                        "images" => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.jpg?s=42d7666179a2851c99fada2e0ceb5da1',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644t.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/4/19644l.webp?s=42d7666179a2851c99fada2e0ceb5da1',
                            ],
                        ],
                        "title" => "Cowboy Bebop"
                    ]
                ]
            ]
        ]);
        $this->get('/v4/characters/1/manga')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'role',
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
        Character::factory()->createOne([
            "mal_id" => 1
        ]);
        $this->get('/v4/characters/1/voices')
            ->seeStatusCode(200)
            ->seeJsonStructure(['data'=>[
                [
                    'language',
                    'person' => [
                        'mal_id',
                        'url',
                        'images' => [
                            'jpg' => [
                                'image_url',
                            ],
                        ],
                        'name'
                    ]
                ]
            ]]);
    }

    public function testPictures()
    {
        $document = $this->dummyScraperResultDocument('/v4/characters/1/pictures', 'characters', [
            'pictures' => [
                [
                    'jpg' => [
                        'image_url' => 'https://cdn.myanimelist.net/images/characters/10/34138.jpg',
                    ],
                ]
            ]
        ]);
        DB::table("characters_pictures")->insert($document);
        $this->get('/v4/characters/1/pictures')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    [
                        'jpg' => [
                            'image_url',
                        ]
                    ]
                ]
            ]);
    }

    public function test404()
    {
        $this->mockJikanParserWith404RespondingUpstream();
        $this->get('/v4/characters/1000000')
            ->seeStatusCode(404);
    }
}
