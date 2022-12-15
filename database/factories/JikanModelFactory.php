<?php

namespace Database\Factories;

use JMS\Serializer\Serializer;
use \Illuminate\Database\Eloquent\Factories\Factory;

abstract class JikanModelFactory extends Factory
{

    /**
     * @inheritDoc
     */
    public function definition()
    {
        /**
         * @var Serializer $serializer
         */
        $serializer = app("SerializerV4");
        return $serializer->toArray($this->definitionInternal());
    }

    protected abstract function definitionInternal(): array;
}
