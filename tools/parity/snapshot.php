#!/usr/bin/env php
<?php

/**
 * Parity snapshot tool for the jikan-rest Lumen -> Laravel migration.
 *
 * Hits every endpoint in endpoints.txt and records, per endpoint:
 *   - raw/<slug>.json    the raw response body (as returned)
 *   - shape/<slug>.json  a normalised *structure* signature (keys + types),
 *                        with volatile fields masked, so live MyAnimeList data
 *                        drift does not cause false mismatches
 *   - meta.json          HTTP status, method, path, byte size per endpoint
 *
 * Usage:
 *   php tools/parity/snapshot.php --base-url=http://127.0.0.1:8000 --out=tools/parity/baseline
 */

declare(strict_types=1);

$options = getopt('', ['base-url::', 'out::', 'endpoints::', 'timeout::']);
$baseUrl = rtrim($options['base-url'] ?? 'http://127.0.0.1:8000', '/');
$outDir = rtrim($options['out'] ?? __DIR__.'/baseline', '/');
$endpointsFile = $options['endpoints'] ?? __DIR__.'/endpoints.txt';
$timeout = (int) ($options['timeout'] ?? 30);

if (!is_file($endpointsFile)) {
    fwrite(STDERR, "endpoints file not found: {$endpointsFile}\n");
    exit(2);
}

@mkdir($outDir.'/raw', 0777, true);
@mkdir($outDir.'/shape', 0777, true);

/** Fields whose values legitimately change between runs. */
$volatile = [
    'createdAt', 'modifiedAt', 'updatedAt',
    'created_at', 'modified_at', 'updated_at',
    'request_hash', 'cached_at', 'last_downtime', 'timestamp',
    // heartbeat score is int when no events recorded, float otherwise
    'score',
];

/**
 * Build a structural signature: key names and value types, arrays reduced to
 * the shape of their first element.
 */
function shape(mixed $value, array $volatile): mixed
{
    if (is_array($value)) {
        if (array_is_list($value)) {
            return $value === [] ? ['@list'] : ['@list', shape($value[0], $volatile)];
        }
        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = in_array($key, $volatile, true) ? '@volatile' : shape($item, $volatile);
        }
        ksort($out);

        return $out;
    }

    return match (true) {
        $value === null => '@null',
        is_bool($value) => '@bool',
        is_int($value) => '@int',
        is_float($value) => '@float',
        default => '@string',
    };
}

function slugify(string $method, string $path): string
{
    $slug = strtolower($method).'_'.trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $path), '_');

    return $slug === strtolower($method).'_' ? strtolower($method).'_root' : $slug;
}

$lines = file($endpointsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
$meta = [];
$count = 0;

foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    $parts = preg_split('/\s+/', $line, 2) ?: [$line];
    $method = 'GET';
    $path = $parts[0];
    if (count($parts) === 2 && in_array(strtoupper($parts[0]), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], true)) {
        $method = strtoupper($parts[0]);
        $path = $parts[1];
    }

    $url = $baseUrl.$path;
    $slug = slugify($method, $path);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch) ?: null;
    curl_close($ch);

    if (!is_string($body)) {
        $body = '';
    }

    file_put_contents("{$outDir}/raw/{$slug}.json", $body);

    $decoded = json_decode($body, true);
    $shape = (json_last_error() !== JSON_ERROR_NONE)
        ? ['@nonjson' => true, '@length' => strlen($body)]
        : shape($decoded, $volatile);
    file_put_contents(
        "{$outDir}/shape/{$slug}.json",
        json_encode($shape, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
    );

    $meta[$slug] = [
        'method' => $method,
        'path' => $path,
        'status' => $status,
        'bytes' => strlen($body),
        'error' => $error,
    ];

    printf(
        "  %-3d %-4s %-46s %6d bytes%s\n",
        $status,
        $method,
        $path,
        strlen($body),
        $error ? "  [{$error}]" : ''
    );
    $count++;
}

file_put_contents(
    "{$outDir}/meta.json",
    json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
);

printf("\nCaptured %d endpoints to %s\n", $count, $outDir);
