<?php

namespace App\Dto\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Env;
use \ReflectionClass;
use Spatie\LaravelData\Support\DataConfig;

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
            $properties->put("limit", Env::get("MAX_RESULTS_PER_PAGE",
                property_exists(static::class, "defaultLimit") ? static::$defaultLimit : 25));
        }

        // we want to cast "true" and "false" string values to boolean before validation, so let's take all properties
        // of the class which are bool or bool|Optional type, and using their name read the values from the incoming
        // collection, and if they are present have such a value, convert them.
        $dataClass = app(DataConfig::class)->getDataClass(static::class);
        foreach ($dataClass->properties as $property) {
            if (!$property->type->acceptsType("bool")) {
                continue;
            }
            // the name can be different in the $properties variable, so let's check if there is an input name mapping
            // for the property and use that instead if present.
            $propertyRawName = $property->inputMappedName ?? $property->name;
            if ($properties->has($propertyRawName)) {
                $propertyVal = $properties->get($propertyRawName);
                if ($propertyVal === "true") {
                    $propertyVal = true;
                }
                if ($propertyVal === "false") {
                    $propertyVal = false;
                }
                $properties->put($propertyRawName, $propertyVal);
            }
        }

        return $properties;
    }
}
