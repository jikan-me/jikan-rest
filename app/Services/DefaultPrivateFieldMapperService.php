<?php

namespace App\Services;

use Spatie\Enum\Laravel\Enum;
use Spatie\LaravelData\Optional;

final class DefaultPrivateFieldMapperService implements PrivateFieldMapperService
{
    public function map($instance, array $values): mixed
    {
        $cls = get_class($instance);

        foreach ($values as $fieldName => $fieldValue) {
            if ($fieldValue instanceof Optional) {
                continue;
            }

            if ($fieldValue instanceof Enum) {
                $fieldValue = $fieldValue->label;
            }

            if (!property_exists($cls, $fieldName)) {
                continue;
            }

            $reflection = new \ReflectionProperty($cls, $fieldName);
            // note: ->setAccessible call would be required under php version 8.1
            $reflection->setValue($instance, $fieldValue);
        }

        return $instance;
    }
}
