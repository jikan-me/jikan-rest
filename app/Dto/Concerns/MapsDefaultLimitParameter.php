<?php

namespace App\Dto\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Env;

trait MapsDefaultLimitParameter
{
    public static function prepareForPipeline(Collection $properties): Collection
    {
        if (!$properties->has("limit"))
        {
            /** @noinspection PhpUndefinedFieldInspection */
            $properties->put("limit", Env::get("MAX_RESULTS_PER_PAGE",
                property_exists(static::class, "defaultLimit") ? static::$defaultLimit : 25));
        }

        return $properties;
    }
}
