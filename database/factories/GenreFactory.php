<?php
namespace Database\Factories;
use App\Testing\JikanDataGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;
use MongoDB\BSON\UTCDateTime;

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
            "count" => $this->faker->randomDigit(),
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4", sha1("http://localhost-test/v4/genres/". $this->mediaType ."/" . $mal_id))
        ];
    }
}
