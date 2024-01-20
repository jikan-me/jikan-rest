<?php

// note: some of these are required for packages which are not entirely compatible with "lumen".

// exact copy of "config_path" from laravel framework -- we want to make it available in lumen
if (!function_exists("config_path")) {
    /**
     * Get the configuration path.
     *
     * @param  string  $path
     * @return string
     */
    function config_path(string $path = ""): string
    {
        return app()->configPath($path);
    }
}

// exact copy of "rescue" from laravel framework -- we want to make it available in lumen
if (!function_exists('rescue')) {
    /**
     * Catch a potential exception and return a default value.
     *
     * @param  callable  $callback
     * @param  mixed  $rescue
     * @param  bool|callable  $report
     * @return mixed
     */
    function rescue(callable $callback, mixed $rescue = null, bool|callable $report = true): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            if (value($report, $e)) {
                report($e);
            }

            return value($rescue, $e);
        }
    }
}

if (!function_exists('to_boolean')) {

    /**
     * Convert to boolean
     *
     * @param $booleable
     * @return boolean
     */
    function to_boolean($booleable): bool
    {
        return filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}


if (!function_exists('max_results_per_page')) {
    function max_results_per_page(?int $fallbackLimit = null): int
    {
        return app()->make("jikan-config")->maxResultsPerPage($fallbackLimit);
    }
}

if (!function_exists('text_match_buckets')) {
    function text_match_buckets(): int
    {
        return app()->make("jikan-config")->textMatchBuckets();
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_path(?string $path = ""): string {
        if ($path == "") {
            return base_path('app');
        }
        return base_path('app/' . $path);
    }
}

if (! function_exists('cache')) {
    /**
     * Get / set the specified cache value.
     *
     * If an array is passed, we'll assume you want to put to the cache.
     *
     * @param  mixed  ...$arguments  key|key,default|data,expiration|null
     * @return mixed|\Illuminate\Cache\CacheManager
     *
     * @throws \InvalidArgumentException
     */
    function cache(...$arguments)
    {
        if (empty($arguments)) {
            return app('cache');
        }

        if (is_string($arguments[0])) {
            return app('cache')->get(...$arguments);
        }

        if (! is_array($arguments[0])) {
            throw new InvalidArgumentException(
                'When setting a value in the cache, you must pass an array of key / value pairs.'
            );
        }

        return app('cache')->put(key($arguments[0]), reset($arguments[0]), $arguments[1] ?? null);
    }
}
