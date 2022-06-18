<?php

return [
    'host' => env('ELASTICSEARCH_HOST'),
    'user' => env('ELASTICSEARCH_USER'),
    'password' => env('ELASTICSEARCH_PASSWORD'),
    'cloud_id' => env('ELASTICSEARCH_CLOUD_ID'),
    'api_key' => env('ELASTICSEARCH_API_KEY'),
    'indices' => [
        'mappings' => [
            'default' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'mal_id' => [
                        'type' => 'keyword'
                    ]
                ],
            ],
            'anime_index' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'mal_id' => [
                        'type' => 'keyword'
                    ],
                    'start_date' => [
                        'type' => 'date'
                    ],
                    'end_date' => [
                        'type' => 'date'
                    ],
                    'title' => [
                        'type' => 'text'
                    ],
                    'title_english' => [
                        'type' => 'text'
                    ],
                    'title_japanese' => [
                        'type' => 'text'
                    ],
                    'title_synonyms' => [
                        'type' => 'text'
                    ],
                    'type' => [
                        'type' => 'keyword'
                    ],
                    'source' => [
                        'type' => 'keyword'
                    ],
                    'episodes' => [
                        'type' => 'short'
                    ],
                    'status' => [
                        'type' => 'keyword'
                    ],
                    'airing' => [
                        'type' => 'boolean'
                    ],
                    'rating' => [
                        'type' => 'float'
                    ],
                    'score' => [
                        'type' => 'float'
                    ],
                    'rank' => [
                        'type' => 'integer'
                    ],
                    'popularity' => [
                        'type' => 'integer'
                    ],
                    'members' => [
                        'type' => 'integer'
                    ],
                    'favorites' => [
                        'type' => 'integer'
                    ],
                    'synopsis' => [
                        'type' => 'text'
                    ],
                    'season' => [
                        'type' => 'keyword'
                    ],
                    'year' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'manga_index' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'mal_id' => [
                        'type' => 'keyword'
                    ],
                    'start_date' => [
                        'type' => 'date'
                    ],
                    'end_date' => [
                        'type' => 'date'
                    ],
                    'title' => [
                        'type' => 'text'
                    ],
                    'title_english' => [
                        'type' => 'text'
                    ],
                    'title_japanese' => [
                        'type' => 'text'
                    ],
                    'title_synonyms' => [
                        'type' => 'text'
                    ],
                    'type' => [
                        'type' => 'keyword'
                    ],
                    'source' => [
                        'type' => 'keyword'
                    ],
                    'chapters' => [
                        'type' => 'short'
                    ],
                    'volumes' => [
                        'type' => 'short'
                    ],
                    'status' => [
                        'type' => 'keyword'
                    ],
                    'publishing' => [
                        'type' => 'boolean'
                    ],
                    'rating' => [
                        'type' => 'float'
                    ],
                    'score' => [
                        'type' => 'float'
                    ],
                    'rank' => [
                        'type' => 'integer'
                    ],
                    'popularity' => [
                        'type' => 'integer'
                    ],
                    'members' => [
                        'type' => 'integer'
                    ],
                    'favorites' => [
                        'type' => 'integer'
                    ],
                    'synopsis' => [
                        'type' => 'text'
                    ],
                    'season' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'characters_index' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'mal_id' => [
                        'type' => 'keyword'
                    ],
                    'name' => [
                        'type' => 'text'
                    ],
                    'name_kanji' => [
                        'type' => 'text'
                    ]
                ]
            ],
            'people_index' => [
                'properties' => [
                    'id' => [
                        'type' => 'keyword',
                    ],
                    'mal_id' => [
                        'type' => 'keyword'
                    ],
                    'name' => [
                        'type' => 'text'
                    ],
                    'given_name' => [
                        'type' => 'text'
                    ],
                    'family_name' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ],
        'settings' => [
            'default' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
        ],
    ],
];
