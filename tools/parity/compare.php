#!/usr/bin/env php
<?php

/**
 * Compare two parity snapshots produced by snapshot.php.
 *
 * Fails (exit 1) when an endpoint is missing, an HTTP status differs, or the
 * response *shape* differs. Shape-only comparison is intentional: live
 * MyAnimeList values drift between runs, so we assert structure and status,
 * not exact payloads.
 *
 * Usage:
 *   php tools/parity/compare.php --baseline=tools/parity/baseline --candidate=tools/parity/candidate
 */

declare(strict_types=1);

$options = getopt('', ['baseline::', 'candidate::']);
$base = rtrim($options['baseline'] ?? __DIR__ . '/baseline', '/');
$cand = rtrim($options['candidate'] ?? __DIR__ . '/candidate', '/');

function loadJson(string $file): mixed
{
    return is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
}

$baseMeta = loadJson("{$base}/meta.json") ?? [];
$candMeta = loadJson("{$cand}/meta.json") ?? [];

if ($baseMeta === [] || $candMeta === []) {
    fwrite(STDERR, "Missing meta.json in baseline or candidate. Run snapshot.php first.\n");
    exit(2);
}

$problems = 0;

// 1. Presence + status parity.
foreach ($baseMeta as $slug => $bm) {
    if (!isset($candMeta[$slug])) {
        echo "MISSING      {$slug}\n";
        $problems++;
        continue;
    }
    if ((int) $bm['status'] !== (int) $candMeta[$slug]['status']) {
        printf("STATUS DIFF  %-40s %d -> %d\n", $slug, $bm['status'], $candMeta[$slug]['status']);
        $problems++;
    }
}

foreach ($candMeta as $slug => $cm) {
    if (!isset($baseMeta[$slug])) {
        echo "EXTRA        {$slug}\n";
        $problems++;
    }
}

// 2. Shape parity.
$shapeFiles = glob("{$base}/shape/*.json") ?: [];
sort($shapeFiles);
foreach ($shapeFiles as $baseFile) {
    $slug = basename($baseFile, '.json');
    $candFile = "{$cand}/shape/{$slug}.json";
    if (!is_file($candFile)) {
        echo "SHAPE MISSING {$slug}\n";
        $problems++;
        continue;
    }
    $bs = json_encode(loadJson($baseFile));
    $cs = json_encode(loadJson($candFile));
    if ($bs !== $cs) {
        echo "SHAPE DIFF   {$slug}\n";
        $problems++;
    }
}

echo "\n";
if ($problems === 0) {
    printf("OK: %d endpoints — shapes and statuses match.\n", count($baseMeta));
    exit(0);
}

printf("%d problem(s) across %d endpoints.\n", $problems, count($baseMeta));
exit(1);
