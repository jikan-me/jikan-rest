<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Person;


class PersonFactory extends Factory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Person::class;

    public function definition(): array
    {
        $mal_id = $this->createMalId();
        $name = $this->faker->name();
        $given_name = $this->faker->firstName();
        $family_name = $this->faker->lastName();

        return [
            "mal_id" => $mal_id,
            "url" => $this->createUrl($mal_id, "people"),
            "website_url" => "https://webiste.example",
            "images" => [],
            "name" => $name,
            "given_name" => $given_name,
            "family_name" => $family_name,
            "alternate_names" => [],
            "birthday" => $this->createRandomDateTime("-80 years")->toAtomString(),
            "favorites" => $this->faker->randomDigitNotNull(),
            "about" => "test"
        ];
    }
}
