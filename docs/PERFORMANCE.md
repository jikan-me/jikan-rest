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

## End-to-end performance lever report (2026-10-13) — for PR #614

Methodology: `ab -k` (keep-alive), `-c` sweeps, Octane/RoadRunner on a Mac (arm64),
local Mongo; the full indexer ran in the background (relative numbers). Post-index +
runner re-run recommended for absolutes.

### Baseline (before any optimization) — Octane RR 8 workers
`/up` 3,973 rps · `/v4/anime/21` 259 rps (median 72 ms) · `/v4/anime/966/full` 120 rps ·
`/v4/seasons/now` 104 rps (median 191 ms) · `/v4/top/anime` 136 rps. Single-request
floor for anime/21 ≈ **23 ms**.

### Levers tested → verdict

| Lever | Result | Action |
|---|---|---|
| **Heartbeat per-request Mongo write/query** | THE bottleneck | **Debounced to ≤1 write/interval (1 s default)** — `SourceHeartbeatListener` |
| **Response compression (gzip)** | seasons 109 KB → **18 KB** (~6×) | **Applied** — `GzipResponse` middleware (opt-in on Accept-Encoding) |
| Worker count (Octane) | 2w → 4w → 8w scales | Use ≈ cores; keep configurable |
| Micro-cache (60 s, in-process) | No win on small reads (off 298 vs on 188 rps, 8w) | Kept OFF; Redis+piggyback only |
| opcache.enable_cli (dev server) | ~nil | Keep default; compile not the cost |
| config:cache (warm workers) | ~nil (899 vs 998 rps) | Keep for prod boot, not request path |
| opcache.preload / classmap-authoritative | not measured in-range (ini plumbing) | Recommend for the runtime image |
| Redis shared cache/rate-limit | local redis reachable (docker), host port closed | Recommend at scale; CACHE_DRIVER=redis |
| FrankenPHP | fastest server (5.4k rps light) but **no ext-mongodb** → Mongo routes 500 | Use container image w/ driver, or edge/static |
| Mongo read | indexed `mal_id` confirmed; ~4 ms in-flight w/ persistent workers | Consider projection for huge docs later |
| ETag/fields/batch (proposal WS-G) | not implemented | Recommend for consumer token/transfer wins |

### After (applied optimizations) — Octane RR 4 workers
`/v4/anime/21` plain **~998 rps** (was 259) · `/v4/seasons/now` **~203 rps** (was 104) ·
single-request floor **~4 ms** (was ~23 ms, dominated by the heartbeat write).
gzip: payload/transfer ~6× smaller when the client accepts it.

### Net takeaways for the PR
- Remove/reduce per-request side-effecting I/O (heartbeat) — biggest win.
- Serve with Octane + adequate workers; gzip responses; keep micro-cache off by default;
  cache config for boot; profile serialization next; add WS-G efficiency later.

## End-to-end coverage + robustness benchmark (2026-10-13) — for PR #614

### Coverage throughput (Octane/RoadRunner 4 workers, `ab -k -c 20`, n=2000)

| Endpoint (type) | RPS | Notes |
|---|---|---|
| `/up` | 5,031 | pure route/middleware |
| `/v4/anime/21` (detail) | 918 | Mongo read + serialization |
| `/v4/magazines` (list) | 766 | |
| `/v4/top/anime` (list) | 290 | |
| `/v4/seasons/now` (list) | 209 | heavy payload |
| `/v4/anime?q=one` (**search** — Typesense) | 91 | +typesense round-trip |
| `/v4/producers?q=studio` (**search** — Typesense) | 237 | |

### Single-request latency composition (avg of 3, warm-ish)
`/v4/anime/abc` (404, no data) **~4 ms** = framework+middleware floor · `/v4/anime/21`
~5–30 ms (worker-health dependent) · `/v4/producers?q=` ~19 ms · `/v4/anime?q=one` ~70 ms
(typesense) · `/v4/seasons/now` ~33 ms · `/v4/anime/999999999999` ~1.7 s (scrape!) ·
uncached sub-resource like `/v4/anime/21/staff` scrapes MAL on cache miss (bench hang).

### Attack / edge vector results (status semantics)

| Vector | Status | Finding |
|---|---|---|
| non-numeric id `/anime/abc` | 404 (fast) | route constraint OK |
| `-1` | 404 | OK |
| `0` | 400 | OK |
| huge id `999999999999` | 404 but **~1.7 s** | triggers a scrapers-ish cache-miss path — DoS-ish |
| `page=-1`, `order_by=bogus`, `sfw=garbage` | 400 | DTO validation OK |
| `page=99999999&per_page=9999` | 200 | **no obvious bound enforcement** on per_page |
| `?q=`, trailing slash, `//`, `/..`, `%2e%2e`, quoted filter | 200/403/404 | sane routing |
| `/v4/characters?q=spike` | **500** | **search collection missing → unhandled exception** (inconsistent w/ anime search which degrades gracefully) |
| c=200 (same URL) | 0 failed | but **p100 = 11 s** — queueing + worker churn |
| concurrent hammering | — | **~500 worker-crash markers** (`Target class [config]`, RR respawn) |

### Robustness recommendations (from this run)
1. **Search graceful degradation** — a missing Typesense collection should 404/empty, not 500 (`characters?q=`). Unify with the anime-search path.
2. **Bound `per_page`/`page`** app-wide (enforce max; also protects aggregation load).
3. **Cache/guard scrape-triggering lookups** — huge-id + uncached sub-resources should not hit MAL synchronously; use the cache-first contract consistently (and keep indexer/data coverage so staff/characters fetch from cache).
4. **Octane worker crash churn** — prioritize investigation: `Target class [config]` under load implies a container state-isolation leak (the AI already fixed mediator factories per request; audit other per-request singletons + `octane.php` `flush`). Until resolved, keep workers ≤ cores and watch RR respawn rate.
5. Everything above is consistent: rate-limit + validation are solid; the physics are payload-size and scrape-path related.

### Robustness fixes applied (2026-10-13, part of the perf pass)
1. **Search: missing/unreachable collection ⇒ EMPTY 200 (never 500/404)** — a
   controller-level guard (`SearchController::runSearch`) now covers every search
   action, with handler/service/telemetry guards as depth. Verified on Octane:
   `/v4/characters?q=`, `/v4/manga?q=`, `/v4/people?q=`, `/v4/clubs?q=` → 200
   empty; `/v4/anime?q=` and `/v4/producers?q=` unaffected.
2. **Typesense touchpoints** (runtime): `TypeSenseScoutSearchService` (search —
   guarded incl. `overrideSortingOrder`/descriptor retrieve), `ScoutBuilderMixin`
   (engine pagination — under search guards), `DefaultSearchAnalyticsService`
   (metrics read **and** write guarded), `TypeSenseCollectionDescriptor` (read —
   no unguarded callers). Config/query-shape usages (`JikanConfig`,
   `DefaultAnime/MangaRepository`, `SearchMetric`, `RepositoryQuery`) do not call
   typesense directly at request time.
3. **Negative cache for missing ids** (`scrape_miss_ttl`, 60 s default):
   huge-id / nonexistent-id requests no longer re-scrape MAL on every hit.
   Scoped per cache store: fast replays apply within a worker (in-process
   `array` cache); fleet-wide benefit needs a **shared** store (Redis).
4. **Pagination**: `per_page` was already clamped to the 25-page limit (`max_results_per_page`); huge `page` returns an empty page, not an unbounded response — confirmed, documented (no contract change needed).
