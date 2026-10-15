<?php

namespace App\Console\Commands\Indexer;

/**
 * HTTP GET for indexer commands with a bounded wait.
 *
 * A bare file_get_contents() waits for `default_socket_timeout` (60s) on a stalled
 * connection and, once the stream is open, per read — so a hung API worker would
 * freeze a multi-day indexing run. The using command must declare a `--timeout`
 * option (seconds).
 */
trait FetchesWithTimeout
{
    private const DEFAULT_FETCH_TIMEOUT = 30;

    private function fetchTimeout(): int
    {
        $timeout = (int) $this->option('timeout');

        return $timeout > 0 ? $timeout : self::DEFAULT_FETCH_TIMEOUT;
    }

    /**
     * @return resource
     */
    private function fetchContext(): mixed
    {
        return stream_context_create(['http' => ['timeout' => $this->fetchTimeout()]]);
    }

    /**
     * @throws \RuntimeException when the URL is unreachable, errors, or times out
     */
    private function httpGet(string $url): string
    {
        $body = @file_get_contents($url, false, $this->fetchContext());
        if ($body === false) {
            $reason = error_get_last()['message'] ?? 'request failed';

            throw new \RuntimeException("unreachable {$url} ({$reason})");
        }

        return $body;
    }
}
