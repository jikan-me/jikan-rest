<?php /** @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\HttpV4\Controllers;
use App\Testing\ScoutFlush;
use App\Testing\SyntheticMongoDbTransaction;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use SyntheticMongoDbTransaction;
    use ScoutFlush;

    public function testAnimeSearch()
    {
        $this->get('/v4/anime?order_by=id&sort=asc')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                    'items' => [
                        'count',
                        'total',
                        'per_page',
                    ]
                ],
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
            ])
        ;
    }

    public function testMangaSearch()
    {
        $this->get('/v4/manga?order_by=id&sort=asc')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                    'items' => [
                        'count',
                        'total',
                        'per_page',
                    ]
                ],
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
            ])
        ;
    }

    public function testPeopleSearch()
    {
        $this->get('/v4/people?q=Sawano')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                    'items' => [
                        'count',
                        'total',
                        'per_page',
                    ]
                ],
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
            ])
        ;
    }

    public function testCharacterSearch()
    {
        $this->get('/v4/characters?q=Okabe,%20Rintarou')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'pagination' => [
                    'last_visible_page',
                    'has_next_page',
                    'items' => [
                        'count',
                        'total',
                        'per_page',
                    ]
                ],
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
                ],
            ])
        ;
    }
}
