<?php
namespace Database\Factories;
use App\Testing\JikanDataGenerator;
use App\Person;
use MongoDB\BSON\UTCDateTime;


class PersonFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $name = $this->faker->name();
        $given_name = $this->faker->firstName();
        $family_name = $this->faker->lastName();

        return [
            "mal_id" => $mal_id,
            "url" => $this->createMalUrl($mal_id, "people"),
            "website_url" => "https://webiste.example",
            "images" => [
                "jpg" => [
                    "image_url" => "https://cdn.myanimelist.net/images/voiceactors/1/55486.jpg"
                ]
            ],
            "name" => $name,
            "given_name" => $given_name,
            "family_name" => $family_name,
            "alternate_names" => [
                $this->faker->name()
            ],
            "birthday" => $this->createRandomDateTime("-80 years")->toAtomString(),
            "member_favorites" => $this->faker->randomDigitNotNull(),
            "about" => "test",
            "createdAt" => new UTCDateTime(),
            "modifiedAt" => new UTCDateTime(),
            "request_hash" => sprintf("request:%s:%s", "v4", $this->getItemTestUrl("people", $mal_id)),
            "published_manga" => [],
            "voice_acting_roles" => [],
            "anime_staff_positions" => []
        ];
    }
}
