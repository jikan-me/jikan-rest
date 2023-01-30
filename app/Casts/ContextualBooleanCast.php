<?php

namespace App\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

/**
 * This class ensures that "?sfw" and "?kids" boolean type query string parameters in the url would be interpreted as "true"
 */
final class ContextualBooleanCast implements Cast
{

    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        $propertyName = $property->name;

        if (array_key_exists($propertyName, $context) && $context[$propertyName] === "")
        {
            return true;
        }

        return $value;
    }
}
