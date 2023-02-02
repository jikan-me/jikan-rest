<?php

namespace Database\Factories;

use App\Club;
use App\Testing\JikanDataGenerator;
use Illuminate\Support\Carbon;
use MongoDB\BSON\UTCDateTime;

final class ClubFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Club::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $url = "https://myanimelist.net/clubs.php?cid=".$mal_id;
        $createdAt = Carbon::createFromTimestamp(
            $this->faker->dateTime()->getTimestamp())->toAtomString();
        $modifiedAt = Carbon::createFromTimestamp(
            $this->faker->dateTime()->getTimestamp())->toDateTimeString();

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "images" => [
                "jpg" => [
                    "image_url" => "https://cdn.myanimelist.net/images/clubs/16/222057.jpg"
                ]
            ],
            "category" => $this->faker->randomElement(["anime", "manga", "characters"]),
            "created" => $createdAt,
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "created_at" => $createdAt,
            "updated_at" => $modifiedAt,
            "name" => $this->faker->name(),
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("club", $mal_id)),
            "anime" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "anime",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/anime/1/x"
                ]
            ],
            "characters" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "character",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/character/1234"
                ]
            ],
            "manga" => [
                [
                    "mal_id" => $this->createMalId(),
                    "type" => "manga",
                    "name" => $this->faker->name(),
                    "url" => "https://myanimelist.net/manga/1/x"
                ]
            ],
            "staff" => [
                [
                    "url" => "https://myanimelist.net/profile/cyruz",
                    "username" => "cryuz"
                ]
            ],
            "members" => $this->faker->numberBetween(1, 9999),
            "access" => $this->faker->randomElement(["public", "private"])
        ];
    }
}
