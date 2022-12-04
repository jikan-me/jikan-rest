<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

abstract class GenreFactory extends Factory
{
    use JikanDataGenerator;

    protected string $mediaType = "";

    public function definition(): array
    {
        $mal_id = $this->createMalId();
        $name = $this->getRandomGenreName();
        $url = $this->createUrl($mal_id, $this->mediaType . "/genre");

        return [
            "mal_id" => $mal_id,
            "name" => $name,
            "url" => $url,
            "count" => $this->faker->randomDigit()
        ];
    }
}
