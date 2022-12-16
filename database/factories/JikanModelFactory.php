<?php

namespace Database\Factories;

use JMS\Serializer\Serializer;
use \Illuminate\Database\Eloquent\Factories\Factory;

abstract class JikanModelFactory extends Factory
{

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
        return $serializer->toArray($stateDefinition);
    }

    protected abstract function definitionInternal(): array;
}
