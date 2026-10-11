<?php

namespace Database\Factories;

use App\CarbonDateRange;
use Jikan\Model\Common\DateRange;
use JMS\Serializer\Serializer;
use \Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Enum\Laravel\Faker\FakerEnumProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

abstract class JikanModelFactory extends Factory
{
    /**
     * Resolve via the container so contextual bindings apply.
     *
     * Laravel 12's Factory::new() uses `new static`, which bypasses the container
     * and cannot inject interface constructor arguments (e.g. the
     * MediaModelFactoryDescriptor required by JikanMediaModelFactory).
     */
    public static function new($attributes = [])
    {
        return App::makeWith(static::class, [
            'count' => null,
            'states' => null,
            'has' => null,
            'for' => null,
            'afterMaking' => null,
            'afterCreating' => null,
            'connection' => null,
            'recycle' => null,
        ])->state($attributes)->configure();
    }

    public function configure(): JikanModelFactory|static
    {
        $this->faker->addProvider(new FakerEnumProvider($this->faker));
        if (array_key_exists("GITHUB_JOB", $_ENV) && $_ENV["GITHUB_JOB"] !== "") {
            $this->faker->seed($_ENV["GITHUB_JOB"]);
        } else {
            $this->faker->seed(Str::random());
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return $this->serializeStateDefinition($this->definitionInternal());
    }

    public function serializeStateDefinition($stateDefinition): array
    {
        /**
         * @var Serializer $serializer
         */
        $serializer = app("SerializerV4");
        $translated = array_merge(array(), $stateDefinition);
        foreach ($stateDefinition as $k => $v)
        {
            if ($v instanceof DateRange || $v instanceof CarbonDateRange)
            {
                $converted = $serializer->toArray([$k => $v]);
                $translated[$k] = $converted[$k];
            }
        }
        return $translated;
    }

    protected abstract function definitionInternal(): array;
}
