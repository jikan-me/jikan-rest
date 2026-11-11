# Search benchmark

A committed, reproducible lexical-search benchmark for issue #615 phase 4.

## Corpus

`tools/search-benchmark/queries.json` contains categories:

- exact MAL ID
- exact title
- alias / synonym
- typo
- CJK
- structured filters
- no-hit

Each query records the endpoint, parameters, expected MAL IDs, and whether the
expected result must rank first.

## Run

```bash
# against a running instance
php tools/search-benchmark/benchmark.php \
  --base-url=http://127.0.0.1:8000

# gate mode
php tools/search-benchmark/benchmark.php \
  --base-url=http://127.0.0.1:8000 --check

# machine-readable
php tools/search-benchmark/benchmark.php --json
```

Or through the application:

```bash
php artisan search:benchmark --base-url=http://127.0.0.1:8000
php artisan search:benchmark --check
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

## Data

- **CI:** synthetic fixture documents only; Typesense 27.0.
- **Local full-data:** run with Docker Typesense against the indexed catalogue.
  The current local Mongo subset is 3,021 anime; the full 10k run is recorded
  separately once the full index is available.
