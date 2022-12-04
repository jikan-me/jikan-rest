<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Character;


class CharacterFactory extends Factory
{
    use JikanDataGenerator;

    protected $model = Character::class;

    public function definition()
    {
        $mal_id = $this->createMalId();
        $url = $this->createUrl($mal_id, "character");

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "images" => [],
            "name" => $this->faker->name(),
            "name_kanji" => "岡",
            "nicknames" => [],
            "favorites" => $this->faker->randomDigitNotNull(),
            "about" => "test"
        ];
    }
}
