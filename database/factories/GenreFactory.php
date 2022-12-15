<?php
namespace Database\Factories;
use App\Testing\JikanDataGenerator;
use MongoDB\BSON\UTCDateTime;

abstract class GenreFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    protected string $mediaType = "";

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $name = $this->getRandomGenreName();
        $url = $this->createMalUrl($mal_id, $this->mediaType . "/genre");

        return [
            "mal_id" => $mal_id,
            "name" => $name,
            "url" => $url,
            "count" => $this->faker->randomDigit(),
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4",
                $this->getItemTestUrl("genres", $mal_id, $this->mediaType))
        ];
    }
}
