<?php

namespace App\Dto\Concerns;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

/**
 * A trait for preparing the incoming data before passing it through the data pipeline.
 * All preparation logic lives here.
 * https://spatie.be/docs/laravel-data/v2/advanced-usage/pipeline
 */
trait PreparesData
{
    public static function prepareForPipeline(Collection $properties): Collection
    {
        // let's always set the limit parameter to the globally configured default value
        if (property_exists(static::class, "limit") && !$properties->has("limit")) {
            /** @noinspection PhpUndefinedFieldInspection */
            $properties->put("limit", max_results_per_page(
                property_exists(static::class, "defaultLimit") ? static::$defaultLimit : null));
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
                if ($property->type->acceptsType("bool")) {
                    if ($propertyVal === "true") {
                        $propertyVal = true;
                    }
                    if ($propertyVal === "false") {
                        $propertyVal = false;
                    }
                }
                // if the property is optional and the value is an empty string, we want to ignore it.
                if ($property->type->isOptional && $propertyVal === "") {
                    $propertyVal = null;
                }

                if (!is_null($propertyVal)) {
                    $properties->put($propertyRawName, $propertyVal);
                } else {
                    $properties->forget($propertyRawName);
                }
            }            
        }

        return $properties;
    }
}
