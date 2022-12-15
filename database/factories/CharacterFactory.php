<?php

namespace Database\Factories;

use App\Testing\JikanDataGenerator;
use App\Character;
use MongoDB\BSON\UTCDateTime;


class CharacterFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    protected $model = Character::class;

    protected function definitionInternal()
    {
        $mal_id = $this->createMalId();
        $url = $this->createMalUrl($mal_id, "character");

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "images" => [],
            "name" => $this->faker->name(),
            "name_kanji" => "岡",
            "nicknames" => [],
            "favorites" => $this->faker->randomDigitNotNull(),
            "about" => "test",
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("character", $mal_id))
        ];
    }
}
