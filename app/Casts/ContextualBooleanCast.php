<?php

namespace App\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Creation\CreationContext;

final class ContextualBooleanCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, \Spatie\LaravelData\Support\Creation\CreationContext $context): mixed
    {
        $propertyName = $property->name;

        if (array_key_exists($propertyName, $properties) && $properties[$propertyName] === "") {
            return true;
        }

        return $value;
    }
}