<?php

namespace App\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

final class ContextualBooleanCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        $propertyName = $property->name;

        if (array_key_exists($propertyName, $properties) && ($properties[$propertyName] === '' || $properties[$propertyName] === null)) {
            return true;
        }

        return $value;
    }
}
