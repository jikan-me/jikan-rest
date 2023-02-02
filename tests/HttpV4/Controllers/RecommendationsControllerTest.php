<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class RecommendationsControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeRecommendations()
    {
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
