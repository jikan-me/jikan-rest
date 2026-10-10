# Parity harness

A framework-agnostic safety net for the Lumen → Laravel migration.

It captures, for a curated set of endpoints, the HTTP **status** and the response
**structure** (keys + value types, with volatile fields masked). After the
migration you re-capture and diff: any structural or status change is a
regression in the public v4 contract.

Exact payloads are **not** compared, because the API scrapes live MyAnimeList
data that drifts between runs. Structure + status is the stable contract.

## Usage

Start the app (either the Lumen or Laravel version), then:

```bash
# 1. Capture the baseline (run against the current Lumen app)
php tools/parity/snapshot.php \
  --base-url=http://127.0.0.1:8000 \
  --out=tools/parity/baseline

# 2. After migrating, capture the candidate
php tools/parity/snapshot.php \
  --base-url=http://127.0.0.1:8000 \
  --out=tools/parity/candidate

# 3. Compare (exit 1 on any status or shape difference)
php tools/parity/compare.php \
  --baseline=tools/parity/baseline \
  --candidate=tools/parity/candidate
```

## Files

| Path | Purpose |
|---|---|
| `endpoints.txt` | the endpoint set (method + path); edit to widen coverage |
| `snapshot.php` | captures `raw/`, `shape/` and `meta.json` for a base URL |
| `compare.php` | diffs two snapshots (status + shape), non-zero exit on failure |
| `baseline/` | the reference snapshot (committed) |
| `candidate/` | scratch output for a run under test (not committed) |

## Notes

- Endpoints that hit MyAnimeList can be slow; `snapshot.php` uses a 30s timeout
  per request. Pass `--timeout=60` if needed.
- Add new endpoints to `endpoints.txt` as coverage needs grow; keep the baseline
  and candidate runs on the same set.
