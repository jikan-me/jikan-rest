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
