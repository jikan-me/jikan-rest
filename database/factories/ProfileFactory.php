<?php

namespace Database\Factories;

use App\Profile;
use App\Testing\JikanDataGenerator;
use MongoDB\BSON\UTCDateTime;

final class ProfileFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    protected $model = Profile::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $username = $this->faker->userName();
        $url = $this->createMalUrl($username, "profile");

        return [
            "mal_id" => $mal_id,
            "username" => $username,
            "url" => $url,
            "request_hash" => sprintf("request:%s:%s", "users", $this->getItemTestUrl("users", $username)),
            "images" => [
                "jpg" => [
                    "image_url" => $this->faker->url()
                ],
                "webp" => [
                    "image_url" => $this->faker->url()
                ]
            ],
            "last_online" => new UTCDateTime($this->faker->dateTimeBetween('-5 years')->format('Uv')),
            "gender" => $this->faker->randomElement(["Male", "Female", "Other"]),
            "birthday" => new UTCDateTime($this->faker->dateTimeBetween('-60 years')->format('Uv')),
            "location" => $this->faker->country(),
            "joined" => new UTCDateTime($this->faker->dateTimeBetween('-5 years')->format('Uv')),
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "anime_stats" => [
                'days_watched' => 106.8,
                'mean_score' => 8.21,
                'watching' => 44,
                'completed' => 449,
                'on_hold' => 15,
                'dropped' => 3,
                'plan_to_watch' => 426,
                'total_entries' => 937,
                'rewatched' => 22,
                'episodes_watched' => 6305,
            ],
            "manga_stats" => [
                'days_read' => 91,
                'mean_score' => 8.45,
                'reading' => 380,
                'completed' => 145,
                'on_hold' => 3,
                'dropped' => 1,
                'plan_to_read' => 58,
                'total_entries' => 587,
                'reread' => 1,
                'chapters_read' => 16263,
                'volumes_read' => 777,
            ],
            "favorites" => [
                "anime" => [
                    [
                        'mal_id' => 9253,
                        'url' => 'https://myanimelist.net/anime/9253/Steins_Gate',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199.jpg?s=bd7fc157718660193b28926006b3fd1c',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199t.jpg?s=bd7fc157718660193b28926006b3fd1c',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199l.jpg?s=bd7fc157718660193b28926006b3fd1c',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199.webp?s=bd7fc157718660193b28926006b3fd1c',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199t.webp?s=bd7fc157718660193b28926006b3fd1c',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/anime/5/73199l.webp?s=bd7fc157718660193b28926006b3fd1c',
                            ],
                        ],
                        'title' => 'Steins;Gate',
                        'type' => 'TV',
                        'start_year' => 2011,
                    ]
                ],
                "manga" => [
                    [
                        'mal_id' => 642,
                        'url' => 'https://myanimelist.net/manga/642/Vinland_Saga',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925.jpg?s=967b5e2908beae63c5c22e61176ef728',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925t.jpg?s=967b5e2908beae63c5c22e61176ef728',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925l.jpg?s=967b5e2908beae63c5c22e61176ef728',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925.webp?s=967b5e2908beae63c5c22e61176ef728',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925t.webp?s=967b5e2908beae63c5c22e61176ef728',
                                'large_image_url' => 'https://cdn.myanimelist.net/images/manga/2/188925l.webp?s=967b5e2908beae63c5c22e61176ef728',
                            ],
                        ],
                        'title' => 'Vinland Saga',
                        'type' => 'Manga',
                        'start_year' => 2005,
                    ]
                ],
                "characters" => [
                    [
                        'mal_id' => 34470,
                        'url' => 'https://myanimelist.net/character/34470/Kurisu_Makise',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/characters/10/114399.jpg?s=f259c43bfc8346dad813e9491d507165',
                            ],
                            'webp' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/characters/10/114399.webp?s=f259c43bfc8346dad813e9491d507165',
                                'small_image_url' => 'https://cdn.myanimelist.net/images/characters/10/114399t.webp?s=f259c43bfc8346dad813e9491d507165',
                            ],
                        ],
                        'name' => 'Makise, Kurisu',
                    ]
                ],
                "people" => [
                    [
                        'mal_id' => 39950,
                        'url' => 'https://myanimelist.net/people/39950/Sayuri',
                        'images' => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/voiceactors/1/57707.jpg?s=2998fa772aea87259bbf3855e6c52043',
                            ],
                        ],
                        'name' => 'Sayuri',
                    ]
                ]
            ],
            "last_updates" => [
                "anime" => [
                    [
                        'entry' => [
                            'mal_id' => 15227,
                            'url' => 'https://myanimelist.net/anime/15227/Kono_Sekai_no_Katasumi_ni',
                            'images' => [
                                'jpg' => [
                                    'image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704.jpg?s=222f784bad4c7505ec2761715f585cc9',
                                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704t.jpg?s=222f784bad4c7505ec2761715f585cc9',
                                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704l.jpg?s=222f784bad4c7505ec2761715f585cc9',
                                ],
                                'webp' => [
                                    'image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704.webp?s=222f784bad4c7505ec2761715f585cc9',
                                    'small_image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704t.webp?s=222f784bad4c7505ec2761715f585cc9',
                                    'large_image_url' => 'https://cdn.myanimelist.net/images/anime/2/87704l.webp?s=222f784bad4c7505ec2761715f585cc9',
                                ],
                            ],
                            'title' => 'Kono Sekai no Katasumi ni',
                        ],
                        'score' => 0,
                        'status' => 'Plan to Watch',
                        'episodes_seen' => NULL,
                        'episodes_total' => NULL,
                        'date' => '2022-05-19T23:38:00+00:00',
                    ]
                ],
                "manga" => []
            ],
            "about" => "",
        ];
    }
}
