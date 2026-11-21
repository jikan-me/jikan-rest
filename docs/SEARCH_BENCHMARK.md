# Search benchmark

Operator guide for the lexical-search benchmark from issue #615 phase 4.
Ranking changes affect ordering only; response shapes are unchanged.

## Corpus

`tools/search-benchmark/queries.json` is the calibrated corpus. It currently
holds 11 anime queries against `/v4/anime`, covering:

- exact MAL ID
- exact title
- alias / synonym
- typo (short and long)
- CJK
- structured filters
- no-hit
- numeric title vs numeric ID collision

Each query records the endpoint, parameters, expected MAL IDs, and whether the
expected result must rank first.

## Run

The harness calls a running API instance; it does not index anything itself.

```bash
# full report
php artisan search:benchmark --base-url=http://127.0.0.1:8000

# relevance gate (exit code 1 on regression)
php artisan search:benchmark --base-url=http://127.0.0.1:8000 --check

# machine-readable
php artisan search:benchmark --json
```

`php tools/search-benchmark/benchmark.php` accepts the same `--base-url`,
`--queries`, `--check`, and `--json` flags and can be run directly without
Artisan.

`--when-ready` skips the run until the anime collection reaches `--threshold`
rows (default `10000`):

```bash
php artisan search:benchmark --when-ready --threshold=10000
```

## Metrics

- `P@1` — expected result at rank 1
- `P@5` — expected result within the first five
- `MRR` — reciprocal rank of the first expected result
- `p50` / `p95` latency

## Gate

`--check` fails when:

- `P@1 < 0.90`
- `MRR < 0.85`
- an exact-ID or exact-title fixture is not at rank 1

Latency is reported, not gated.

## CI

The `search-benchmark` job in `.github/workflows/ci.yml` does not run this
harness. It starts Typesense 27.0 and runs the ranking fixture test against it:

```bash
vendor/bin/phpunit --no-coverage --testsuite=integration --filter='SearchRankingTest'
```

The test indexes synthetic fixture documents itself, so no production data is
required. The benchmark harness above is a local/opt-in tool for measuring
relevance and latency against a populated index.

## Full-data run — 2026-11-20

Environment: Typesense 27.0 (Docker), 29,735 anime imported from Mongo. Re-run
against the same index with `php artisan search:benchmark --check`.

| Metric | Result |
|---|---:|
| P@1 | 0.90 |
| MRR | 0.91 |
| p50 | 73.4 ms |
| p95 | 134.1 ms |

Passing cases: exact ID, exact title, alias, typo, CJK, filtered search,
numeric-title collision, no-hit.

Known failure: the very short query `cow` ranks the expected title at position
8. It is reported but not a gate failure because it is not an exact case; the
relevance thresholds still pass.
