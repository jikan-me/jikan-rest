<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RecommendationsControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeRecommendations()
    {
        $document = $this->dummyResultsDocument('/v4/recommendations/anime', 'recommendations', [[
            'mal_id' => '4103-6675',
            'entry' => [
                [
                    'mal_id' => 4103,
                    'url' => 'https://myanimelist.net/anime/4103/Oval_x_Over',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979.jpg',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979t.jpg',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979.webp',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979t.webp',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/5/29979l.webp',
                        ],
                    ],
                    'title' => 'Oval x Over',
                ],
                [
                    'mal_id' => 6675,
                    'url' => 'https://myanimelist.net/anime/6675/Redline',
                    'images' => [
                        'jpg' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553.jpg',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553t.jpg',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553l.jpg',
                        ],
                        'webp' => [
                            'image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553.webp',
                            'small_image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553t.webp',
                            'large_image_url' => 'https://cdn.myanimelist.net/images/anime/12/28553l.webp',
                        ],
                    ],
                    'title' => 'Redline',
                ],
            ],
            'content' => 'Oval x Over looks like a prototype version of Redline....',
            'date' => '2022-06-20T17:21:22+00:00',
            'user' => [
                'url' => 'https://myanimelist.net/profile/VBayer',
                'username' => 'VBayer',
            ],
        ]]);
        DB::table("recommendations")->insert($document);
        $this->get('/v4/recommendations/anime')
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
                        'user' => [
                            'username',
                            'url',
                        ],
                    ]
                ]
            ]);
    }

}
