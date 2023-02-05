<?php

namespace Database\Factories;

use App\Testing\JikanDataGenerator;
use App\Character;
use MongoDB\BSON\UTCDateTime;


class CharacterFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    protected $model = Character::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $url = $this->createMalUrl($mal_id, "character");

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "images" => [
                "jpg" => [
                    "image_url" => "https://cdn.myanimelist.net/images/characters/4/50197.jpg"
                ],
                "webp" => [
                    "image_url" => "https://cdn.myanimelist.net/images/characters/4/50197.webp",
                    "small_image_url" => "https://cdn.myanimelist.net/images/characters/4/50197t.webp"
                ]
            ],
            "name" => $this->faker->name(),
            "name_kanji" => "岡",
            "nicknames" => [],
            "member_favorites" => $this->faker->randomDigitNotNull(),
            "about" => "test",
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "characters", $this->getItemTestUrl("character", $mal_id)),
            "animeography" => [],
            "mangaography" => [],
            "voice_actors" => [
                [
                    "person" => [
                        "mal_id" => 11,
                        "url" => "https://myanimelist.net/people/11/Kouichi_Yamadera",
                        "images" => [
                            'jpg' => [
                                'image_url' => 'https://cdn.myanimelist.net/images/voiceactors/1/16847.jpg',
                            ],
                        ],
                        "name" => "Yamadera, Kouichi"
                    ],
                    "language" => "Japanese"
                ]
            ]
        ];
    }
}
