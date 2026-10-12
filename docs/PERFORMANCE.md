# Performance — stress-test matrix & bottlenecks (2026-10-13)

Methodology: `ab -k -n 3000 -c N` (keep-alive) on a Mac (arm64) running the app
locally. **Caveats:** the full indexer was running concurrently in the background
(MAL scraping + Mongo writes), so absolute numbers are depressed; treat the
matrix as **relative** and re-run post-index + on a real runner for clean
absolutes. Tests were run on spare ports alongside the indexer's `:8000` server
(the main working tree was untouched).

## Matrix (requests/sec at concurrency 20, unless noted)

| Runtime | `/up` | `/v4/anime/21` | `/v4/seasons/now` | Notes |
|---|---|---|---|---|
| `artisan serve` (native) | 236 | 41 | 31 | single-threaded ceiling |
| raw `php -S` + `opcache.enable_cli=1` | 279 | 44 | — | opcache helps little — the cost isn't compile |
| **Octane/RoadRunner, 2 workers** | 2,046 | 92 | 63 | ~2× on data endpoints |
| **Octane/RoadRunner, 8 workers** | 3,973 | **212** (at c=10) | — | scales with workers |
| **Octane/FrankenPHP** | 5,457 | **500** (no Mongo driver) | — | fastest server; unusable for data in the static binary |

### Latency (single request, `c=1`)
`/v4/anime/21` ≈ **23–27 ms** on every runtime — the server is not the cost.

## Bottleneck analysis

1. **Mongo read is the single largest per-request cost: `findOne({mal_id:21})` ≈ 7.4 ms.**
   The rest of the ~27 ms is JMS serialization + the middleware chain (throttle,
   `EndpointCacheTtl`, `MicroCaching`, `SourceHeartbeatMonitor`) + response build.
   Collection is large and under active indexer writes.
2. **The 60 s in-memory micro-cache is NOT engaging.** Two identical back-to-back
   `/v4/anime/21` calls take the same time, and RPS doesn't jump when repeating the
   same URL — a configured micro-cache should short-circuit hot repeats.
3. **Server concurrency:** native `php -S` caps around ~40 RPS; Octane provides the
   real scaling (8 workers → ~212 RPS at c=10). FrankenPHP's static binary lacks
   `ext-mongodb` so only non-DB routes run (they're extremely fast).
4. **Transfer weight:** list endpoints are heavy — `/v4/seasons/now` ≈ **105 KB**
   (~3.2 MB/s in the test). No gzip in-app; a proxy/CDN is expected to compress.
5. `opcache.enable_cli` on the dev server: negligible impact (avoids the obvious
   "turn on opcache" rabbit hole — latency is work-bound, not compile-bound).

## Improvements (prioritized)

### P0 — quick wins
- **Enable/verify the 60 s micro-cache** so repeated identical URLs short-circuit
  (biggest latency/CPU lever for hot endpoints once the cache key matches URL +
  method; check `MicroCaching` middleware config + which routes it covers).
- **Verify Mongo indexes** on `mal_id` (+ the compound season/schedule indexes from
  `DB_INDEXES.md`) with `explain` on the read/aggregation paths; add `projection` to
  trim oversized documents if safe.

### P1 — serialization & transfer
- Profile the **JMS serialization** of `Anime`/list resources; consider preload
  (`opcache.preload` for Octane/FrankenPHP builds) and classmap-authoritative
  autoload for the runtime image.
- Add **gzip/br** (response middleware or documented proxy settings: nginx
  `gzip on`, HTTP/2, static passthrough for `/llms.txt` + docs assets).

### P2 — scale & architecture
- **Octane tuning:** workers ≈ core count, bounded `max-requests` (the AI's
  configurable envs), reload-on-deploy. Add **Redis** for a shared hot-id LRU +
  cross-worker cache/rate-limit when scale demands (replaces per-worker arrays).
- **Contract-level efficiency (proposal WS-G):** `fields`, bounded batch reads,
  ETag/conditional — directly cut bytes/tokens and 304s for repeat consumers.
- **FrankenPHP:** pursue via a **container image with ext-mongodb** (documented in
  `PROVIDERS.md`); the static binary is fastest for edge/static but can't serve data.

### P3 — measure
- Keep `docs/PERFORMANCE.md` as the repeatable harness; re-run post-index (clean
  absolutes) and on a runner. Consider a tiny perf/CI gate (RPS regression alert).

---

_Last updated: 2026-10-13 · indexer background load caveat applies._

## Gains log (2026-10-13, local — NOT pushed, pending PR reporting)

### Micro-cache (60s in-process) — enabled locally as P0, then evaluated
Verification: it engages (sequential repeat: **295 ms → 19 ms** on `/v4/anime/21`).
Impact on throughput (`ab -k -c 20`, Octane/RoadRunner **8 workers**):

| config | `/v4/anime/21` RPS | `/v4/seasons/now` RPS |
|---|---|---|
| micro-cache OFF | **298** | 102 |
| micro-cache ON | 188 | 101 |

**Conclusion:** with enough workers the app's parallel work dominates; the micro-cache's
per-request sha1/`json_decode`/`json_encode` overhead can **exceed** the small indexed Mongo
read it skips, and it's per-process (octane) state that loses `Cache-Control` headers on hits.
Single-request floor is ~**23 ms** on every runtime — Mongo `findOne` ≈ 7 ms, the rest is the
middleware chain (e.g. per-request source-heartbeat) + JMS serialization + response build.

### What actually moved the needle (relative)
- **Workers:** native serve 41 RPS → Octane 8w ~**298 RPS** on `/v4/anime/21` (≈7×).
- **Server overhead:** `/up` 236 → 2,046 (2w) → **3,973 (8w)**.

### Standing recommendations (revised)
- Keep `MICROCACHING=false` (opt-in default) for now; if adopted, pair with a **shared Redis**
  cache + only for payload-heavy endpoints, not small reads.
- Profile the **~23 ms floor**: source-heartbeat middleware I/O per request, JMS serializer,
  response escaping — target the biggest slice (likely middleware + serialization).
- **gzip/br + HTTP/2** in front (proxy) for the heavy list payloads (~105 KB seasons/now).
- Octane worker/max-requests tuning; **Redis** shared cache/rate-limit at scale.
- **fields/batch/ETag (proposal WS-G)** for the real consumer token/transfer wins.
- Re-measure post-index (clean absolutes) + provide these numbers to PR #614.

