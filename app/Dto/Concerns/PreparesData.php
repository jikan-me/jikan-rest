<?php

namespace App\Dto\Concerns;

use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

/**
 * A trait for preparing the incoming data before passing it through the data pipeline.
 * All preparation logic lives here.
 * https://spatie.be/docs/laravel-data/v3/advanced-usage/pipeline
 */
trait PreparesData
{
    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    public static function prepareForPipeline(array $properties): array
    {
        $properties = collect($properties);

        // let's always set the limit parameter to the globally configured default value
        if (property_exists(static::class, 'limit') && !$properties->has('limit')) {
            $properties->put('limit', max_results_per_page(
                property_exists(static::class, 'defaultLimit') ? static::$defaultLimit : null));
        }

        // we want to cast "true" and "false" string values to boolean before validation, so let's take all properties
        // of the class which are bool or bool|Optional type, and using their name read the values from the incoming
        // collection, and if they are present have such a value, convert them.
        $dataClass = app(DataConfig::class)->getDataClass(static::class);
        foreach ($dataClass->properties as $property) {
            /**
             * @var DataProperty $property
             */
            // the name can be different in the $properties variable, so let's check if there is an input name mapping
            // for the property and use that instead if present.
            $propertyRawName = $property->inputMappedName ?? $property->name;
            if ($properties->has($propertyRawName)) {
                $propertyVal = $properties->get($propertyRawName);
                if ($property->type->acceptsType('bool')) {
                    if ($propertyVal === 'true') {
                        $propertyVal = true;
                    }
                    if ($propertyVal === 'false') {
                        $propertyVal = false;
                    }
                    // ContextualBooleanCast semantics: a bare flag (`?sfw`, `?continuing`)
                    // normalises to "" or null and means true. Casts run after validation,
                    // so do the conversion here as well.
                    if ($propertyVal === '' || $propertyVal === null) {
                        $propertyVal = true;
                    }
                }
                // if the property is optional (or nullable) and the value is an empty
                // string, we want to ignore it.
                if ($propertyVal === ''
                    && !$property->type->acceptsType('bool')
                    && ($property->type->isOptional || $property->type->acceptsValue(null))
                ) {
                    $propertyVal = null;
                }

                if (!is_null($propertyVal)) {
                    $properties->put($propertyRawName, $propertyVal);
                } else {
                    $properties->forget($propertyRawName);
                }
            }
        }

        return $properties->all();
    }
}
