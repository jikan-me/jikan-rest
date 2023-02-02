<?php

namespace Database\Factories;

use App\Magazine;
use App\Testing\JikanDataGenerator;

final class MagazineFactory extends JikanModelFactory
{
    use JikanDataGenerator;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Magazine::class;

    protected function definitionInternal(): array
    {
        $mal_id = $this->createMalId();
        $name = $this->createTitle();
        $url = $this->createMalUrl($mal_id, "manga/magazine");
        $count = $this->faker->numberBetween(1, 999);

        return [
            "mal_id" => $mal_id,
            "name" => $name,
            "url" => $url,
            "count" => $count
        ];
    }
}
