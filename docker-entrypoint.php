#!/usr/bin/env php
<?php

use Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

/**
 * Minimal .env writer used by the container entrypoint.
 *
 * The original script relied on MirazMac\DotEnv\Writer, a package that is not
 * published on Packagist, so the image could never autoload it. This keeps the
 * same behaviour (upsert the given keys, preserve formatting of every other
 * line) with no extra dependency, and returns how many keys were newly added.
 */
function env_file_set(string $path, array $values): int
{
    $previous = [];
    $lines = file_exists($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
    $kept = [];
    foreach ($lines as $line) {
        if (preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=/', $line, $m)) {
            $previous[] = $m[1];
            if (array_key_exists($m[1], $values)) {
                continue; // replaced below
            }
        }
        if (trim($line) !== '') {
            $kept[] = $line;
        }
    }

    $added = 0;
    foreach ($values as $key => $value) {
        if (!in_array($key, $previous, true)) {
            $added++;
        }
        $kept[] = $key.'='.env_quote((string) $value);
    }
    file_put_contents($path, implode(PHP_EOL, $kept).PHP_EOL);

    return $added;
}

/** Write a value in a form phpdotenv can read back. */
function env_quote(string $value): string
{
    if ($value === '') {
        return "''";
    }

    return preg_match('/^[A-Za-z0-9_.\-:,;@\/]+$/', $value)
        ? $value
        : '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
}

/** Container env read, with the same inheritance shape the app itself uses. */
function env_value(string $key, $default = null)
{
    $v = getenv($key);
    if ($v === false) {
        $v = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    return $v;
}

$safe_defaults = [
    // mongodb regex search by default
    'SCOUT_DRIVER' => 'none',
    'SCOUT_QUEUE' => false,
    'THROTTLE' => false,
    'QUEUE_CONNECTION' => 'database',
    'DB_CACHING' => true,
    'DB_HOST' => 'localhost',
    'DB_PORT' => 27017,
    'DB_DATABASE' => 'jikan',
    'DB_USERNAME' => '',
    'DB_PASSWORD' => '',
];

// The env vars set on the container itself (Docker --env / Compose environment).
$current_env = getenv();

// First boot of a pristine image: materialise .env from .env.dist and overlay
// the safe defaults from the container environment.
if (!file_exists('.env')) {
    copy('.env.dist', '.env');
    $values = [];
    foreach ($safe_defaults as $env_var_name => $env_var_default) {
        $values[$env_var_name] = env_value($env_var_name, $env_var_default);
    }
    env_file_set('.env', $values);
}

// Support Docker Swarm-style secrets: an env var ending in __FILE points at a
// file whose contents become the value of the stripped key. Write them into
// .env so the Octane workers (which may not inherit the environment) see them.
$secrets = [];
foreach ($current_env as $env_key => $env_value_raw) {
    if (!str_contains($env_key, '__FILE')) {
        continue;
    }
    $secret_path = $env_value_raw;
    if (!is_string($secret_path) || !file_exists($secret_path)) {
        echo "Couldn't load secret: ".$secret_path.PHP_EOL;

        continue;
    }
    $original_key = str_replace('__FILE', '', $env_key);
    $secrets[$original_key] = str_replace(["\n", "\r"], '', file_get_contents($secret_path));
}

if ($secrets !== []) {
    env_file_set('.env', $secrets);
    echo "Secrets loaded successfully.\n";
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$current_env = array_merge(getenv(), $_ENV, $_SERVER);

if (($current_env['SCOUT_DRIVER'] ?? null) === 'typesense' && empty($current_env['TYPESENSE_API_KEY'] ?? '')) {
    echo 'Please set the TYPESENSE_API_KEY environment variable when setting SCOUT_DRIVER to typesense.';
    exit(1);
}

$periodical_full_indexer_key = 'JIKAN_ENABLE_PERIODICAL_FULL_INDEXER';
if (isset($current_env[$periodical_full_indexer_key]) && in_array($current_env[$periodical_full_indexer_key], [1, '1', 'true', 'True', 'TRUE'], true)) {
    $supercronic_schedule = file_get_contents('/etc/supercronic/laravel');
    $supercronic_schedule .= PHP_EOL;
    $supercronic_schedule .= '0 1 * * 1 php /app/artisan indexer:anime --failed && php /app/artisan indexer:anime --resume && php /app/artisan indexer:manga --failed && php /app/artisan indexer:manga --resume';
    $supercronic_schedule .= PHP_EOL;
    file_put_contents('/etc/supercronic/laravel', $supercronic_schedule);
    $current_time = time();
    echo json_encode(['level' => 'info', 'ts' => "$current_time.0", 'logger' => 'container_entrypoint', 'msg' => 'Full anime/manga indexer is enabled. They will run every Monday at 1am.']).PHP_EOL;
}
