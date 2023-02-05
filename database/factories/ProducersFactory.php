<?php

namespace Database\Factories;

use App\Producers;
use App\Testing\JikanDataGenerator;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class ProducersFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    protected $model = Producers::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $url = $this->createMalUrl($mal_id, "anime/producer");

        return [
            "mal_id" => $mal_id,
            "url" => $url,
            "request_hash" => sprintf("request:%s:%s", "producers", $this->getItemTestUrl("producers", $mal_id)),
            "images" => [
                "jpg" => [
                    "image_url" => "https://cdn.myanimelist.net/images/company/441.png"
                ]
            ],
            "name" => $this->faker->name(),
            "titles" => [
                [
                    "type" => "Default",
                    "title" => $this->faker->name()
                ]
            ],
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "favorites" => 535,
            "established" => new UTCDateTime(Carbon::now()->sub($this->faker->randomNumber(2, true) . " days")->getPreciseTimestamp(3)),
            "about" => "",
            "external_links" => [
                [
                    "name" => $this->faker->name(),
                    "url" => $this->faker->url()
                ]
            ],
            "count" => $this->faker->randomNumber()
        ];
    }
}
