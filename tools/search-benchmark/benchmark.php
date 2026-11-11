<?php

/**
 * Search relevance and latency benchmark.
 *
 * Usage:
 *   php tools/search-benchmark/benchmark.php --base-url=http://127.0.0.1:8000
 *   php tools/search-benchmark/benchmark.php --base-url=http://127.0.0.1:8000 --check
 *   php tools/search-benchmark/benchmark.php --json
 */
$options = getopt('', ['base-url::', 'queries::', 'check', 'json']);
$baseUrl = rtrim((string) ($options['base-url'] ?? 'http://127.0.0.1:8000'), '/');
$queriesPath = (string) ($options['queries'] ?? __DIR__.'/queries.json');
$check = array_key_exists('check', $options);
$asJson = array_key_exists('json', $options);

$document = json_decode((string) file_get_contents($queriesPath), true);
if (!is_array($document) || !isset($document['queries'])) {
    fwrite(STDERR, "Invalid query file: {$queriesPath}\n");
    exit(2);
}

$rows = [];
$latencies = [];
$firstHits = 0;
$reciprocalRanks = 0.0;
$scored = 0;
$failures = [];

foreach ($document['queries'] as $query) {
    $id = (string) ($query['id'] ?? 'unnamed');
    $endpoint = (string) ($query['endpoint'] ?? '/v4/anime');
    $params = (array) ($query['params'] ?? []);
    $expected = array_values(array_map('intval', (array) ($query['expected'] ?? [])));
    $url = $baseUrl.$endpoint.'?'.http_build_query($params);

    $started = microtime(true);
    $context = stream_context_create(['http' => ['timeout' => 30, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $context);
    $elapsedMs = (microtime(true) - $started) * 1000;
    $latencies[] = $elapsedMs;

    $payload = is_string($body) ? json_decode($body, true) : null;
    $items = is_array($payload) && is_array($payload['data'] ?? null) ? $payload['data'] : [];
    $ids = array_values(array_map(
        fn ($item) => (int) ($item['mal_id'] ?? 0),
        array_filter($items, 'is_array')
    ));

    $position = null;
    foreach ($expected as $wanted) {
        $index = array_search($wanted, $ids, true);
        if ($index !== false) {
            $position = $index + 1;
            break;
        }
    }

    if ($expected === []) {
        $pass = $ids === [];
    } else {
        $pass = $position !== null;
    }

    $scored++;
    if ($position === 1) {
        $firstHits++;
    }
    if ($position !== null) {
        $reciprocalRanks += 1 / $position;
    }
    if (!empty($query['must_rank_first']) && $position !== 1) {
        $failures[] = "{$id}: expected first result {$expected[0]}, got ".json_encode(array_slice($ids, 0, 5));
    }
    if (!$pass) {
        $failures[] = "{$id}: expected one of ".json_encode($expected).', got '.json_encode(array_slice($ids, 0, 5));
    }

    $rows[] = [
        'id' => $id,
        'category' => $query['category'] ?? null,
        'position' => $position,
        'p@1' => $position === 1,
        'p@5' => $position !== null && $position <= 5,
        'latency_ms' => round($elapsedMs, 2),
        'ids' => array_slice($ids, 0, 5),
    ];
}

$p1 = $scored > 0 ? $firstHits / $scored : 0.0;
$mrr = $scored > 0 ? $reciprocalRanks / $scored : 0.0;
sort($latencies);
$p50 = $latencies === [] ? 0.0 : $latencies[(int) floor((count($latencies) - 1) * 0.50)];
$p95 = $latencies === [] ? 0.0 : $latencies[(int) ceil((count($latencies) - 1) * 0.95)];

$summary = [
    'queries' => $scored,
    'p@1' => round($p1, 4),
    'mrr' => round($mrr, 4),
    'p50_ms' => round($p50, 2),
    'p95_ms' => round($p95, 2),
    'failures' => $failures,
];

if ($asJson) {
    echo json_encode(['summary' => $summary, 'results' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
} else {
    printf("%-42s %-12s %8s %8s %8s\n", 'query', 'category', 'pos', 'P@1', 'ms');
    printf("%s\n", str_repeat('-', 84));
    foreach ($rows as $row) {
        printf(
            "%-42s %-12s %8s %8s %8.1f\n",
            $row['id'],
            (string) $row['category'],
            $row['position'] ?? '-',
            $row['p@1'] ? 'yes' : 'no',
            $row['latency_ms']
        );
    }
    printf("\nP@1 %.2f · MRR %.2f · p50 %.1f ms · p95 %.1f ms\n", $p1, $mrr, $p50, $p95);
    if ($failures !== []) {
        fwrite(STDERR, "\nFailures:\n- ".implode("\n- ", $failures)."\n");
    }
}

if ($check && ($p1 < 0.90 || $mrr < 0.85 || $failures !== [])) {
    exit(1);
}
