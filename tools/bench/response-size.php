<?php

/**
 * Measure response bytes and a rough token estimate for the response-efficiency
 * work. Usage:
 *
 *   php tools/bench/response-size.php http://127.0.0.1:8000
 */

$base = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');

$paths = [
    '/v4/anime/1',
    '/v4/anime/1?fields=mal_id,title',
    '/v4/anime?limit=5',
    '/v4/anime?limit=5&fields=mal_id,title',
    '/v4/anime?ids=1,5,6',
];

printf("%-48s %10s %10s\n", 'path', 'bytes', '~tokens');
printf("%s\n", str_repeat('-', 72));

foreach ($paths as $path) {
    $body = @file_get_contents($base.$path);

    if ($body === false) {
        printf("%-48s %10s %10s\n", $path, 'ERROR', '-');
        continue;
    }

    printf("%-48s %10d %10d\n", $path, strlen($body), (int) ceil(strlen($body) / 4));
}
