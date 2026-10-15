<?php

use Spatie\Enum\Laravel\Enum;

// Custom app helpers. (Native framework helpers — config_path, rescue, app_path,
// cache, ... — were shimmed here during the Lumen days; Laravel provides them, so
// only the genuinely custom functions remain.)

if (! function_exists('max_results_per_page')) {
    function max_results_per_page(?int $fallbackLimit = null): int
    {
        return app()->make('jikan-config')->maxResultsPerPage($fallbackLimit);
    }
}

if (! function_exists('text_match_buckets')) {
    function text_match_buckets(): int
    {
        return app()->make('jikan-config')->textMatchBuckets();
    }
}

if (! function_exists('ensureEnumPrimitiveValue')) {
    function ensureEnumPrimitiveValue(int|string|bool|float|null|Enum $value): mixed
    {
        if ($value instanceof Enum) {
            return $value->value;
        }

        return $value;
    }
}
