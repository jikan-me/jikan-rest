# TYPESENSE_SPIKE — native Laravel Scout Typesense engine, end-to-end runbook

Spike scope: exercise Laravel Scout's **native Typesense engine** (first-party
`Laravel\Scout\Engines\TypesenseEngine` in `laravel/scout` v11.9 / Laravel 12
branch `laravel-12`) against a **real Typesense server** — the previously
un-verified search path and the `jikanPaginate()` chunked-pagination path
(`app/Mixins/ScoutBuilderMixin.php`), the latter triggers when the engine
reports more than 250 hits.

Environment used for this spike:
- PHP: `tools/dev/php` (Homebrew `php@8.4`)
- Composer: `tools/dev/php /opt/homebrew/bin/composer`
- Mongo: `localhost:27017/jikan` (read-only for the spike; `mongosh` for checks)
- Typesense: native macOS darwin-arm64 binary v26.0 on `localhost:8108`, key `abcd`
  (no Docker available on this machine)

---

## 1. Start Typesense

### Option A — native binary (no Docker)

```bash
mkdir -p /tmp/typesense /tmp/typesense-data /tmp/typesense/log
cd /tmp/typesense
curl -fsSL -O \
  https://dl.typesense.org/releases/26.0/typesense-server-26.0-darwin-arm64.tar.gz
# verify (arm64 v26.0):
#   shasum -a 256 typesense-server-26.0-darwin-arm64.tar.gz
#   7a3c3c07e0e6090b3a7144a22ee798f6d49d5a7ac5485bcb312dfa538faff0de
tar -xzf typesense-server-26.0-darwin-arm64.tar.gz

/tmp/typesense/typesense-server \
  --data-dir=/tmp/typesense-data \
  --api-key=abcd \
  --port=8108 \
  --log-dir=/tmp/typesense/log &
```

Health check:

```bash
curl -sS http://localhost:8108/health        # -> {"ok":true}
```

### Option B — Docker

```bash
docker compose -f docker-compose.typesense.yml up -d
curl -sS http://localhost:8108/health        # -> {"ok":true}
```

---

## 2. App configuration (temporary, via env only)

Run a dev server on a different port with the Typesense driver. Do **not**
permanently edit `config/scout.php` or `.env`.

```bash
cd /Users/irfan/Developer/jikan-rest
SCOUT_DRIVER=typesense \
TYPESENSE_API_KEY=abcd \
APP_URL=http://127.0.0.1:8002 \
tools/dev/php artisan serve --host=127.0.0.1 --port=8002
```

`APP_URL` keeps `searchableAs()` index names stable. Index names on this branch:
`producers_index`, `magazines_index`, `jikan_search_metrics` (no `_testing`
suffix outside `testing` env).

---

## 3. Pre-create collections (REQUIRED — see §6)

The native engine cannot create a collection from an empty schema
(`scout.typesense.model-settings` is not configured in this repo). Create the
collections first, using the schemas the models already declare
(`app/Producers::getCollectionSchema()`, `app/JikanApiSearchableModel::getCollectionSchema()`,
`app/SearchMetric::getCollectionSchema()`):

```bash
K=http://localhost:8108; H="X-TYPESENSE-API-KEY: abcd"; T="Content-Type: application/json"

curl -sS -X POST "$K/collections" -H "$H" -H "$T" -d '{
  "name":"producers_index",
  "fields":[
    {"name":".*","type":"auto"},
    {"name":"titles","type":"string","optional":false,"infix":true,"sort":true},
    {"name":"url","type":"string","optional":false,"infix":true,"sort":true}
  ]}'

curl -sS -X POST "$K/collections" -H "$H" -H "$T" -d '{
  "name":"magazines_index",
  "fields":[
    {"name":".*","type":"auto"},
    {"name":"name","type":"string","optional":false,"sort":true}
  ]}'

curl -sS -X POST "$K/collections" -H "$H" -H "$T" -d '{
  "name":"jikan_search_metrics",
  "fields":[
    {"name":"id","type":"string","optional":false},
    {"name":"search_term","type":"string","optional":false,"sort":false,"infix":true},
    {"name":"request_count","type":"int64","sort":true,"optional":false},
    {"name":"hits","type":"int64[]","sort":false,"optional":false},
    {"name":"hits_count","type":"int64","sort":true,"optional":false},
    {"name":"index_name","type":"string","sort":false,"optional":false,"facet":true}
  ]}'
```

## 4. Import

```bash
SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd APP_URL=http://127.0.0.1:8002 \
  tools/dev/php artisan scout:import "App\\Producers"
SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd APP_URL=http://127.0.0.1:8002 \
  tools/dev/php artisan scout:import "App\\Magazine"
```

Verify:

```bash
curl -sS http://localhost:8108/collections -H "X-TYPESENSE-API-KEY: abcd"
```
Expected during this spike: `producers_index` = 382 docs, `magazines_index` =
1498 docs, `jikan_search_metrics` = 0 (fills lazily on search).

## 5. Verification endpoints

| Check | Command | Expected |
|---|---|---|
| native search (producers) | `curl "http://127.0.0.1:8002/v4/producers?q=studio"` | 200, 35 total |
| native search (producer by name) | `curl "http://127.0.0.1:8002/v4/producers?q=pierrot"` | 200, 1 total (Studio Pierrot) |
| native search (magazines) | `curl "http://127.0.0.1:8002/v4/magazines?q=shonen"` | 200, 39 total |
| sort | `curl "http://127.0.0.1:8002/v4/producers?q=*&order_by=favorites&sort=desc"` | 200, top = id 2 (40 306 favorites) |
| chunked >250 (magazines) | `curl "http://127.0.0.1:8002/v4/magazines?q=*"` | 200, `pagination.items.total: 1498` (int), 60 pages |
| chunked boundary | `curl "http://127.0.0.1:8002/v4/magazines?q=*&page=10"` / `page=11` | gap at hit 250/251, no overlap |
| chunked >250 (producers) | `curl "http://127.0.0.1:8002/v4/producers?q=*"` | 200, total 382, 16 pages (page 16 = 7 items) |

## 6. Spike results

### The two as-is blockers (root cause of "previously-unverified")

The app was migrated to the native `TypesenseEngine`, but `config/scout.php`
has **no `scout.typesense.model-settings`**, and the models expose the legacy
driver conventions `typesenseQueryBy()` / `getCollectionSchema()` which the
**native engine does not read** (it reads `model-settings.*.search-parameters.query_by`
/ `model-settings.*.collection-schema`, or model methods
`typesenseSearchParameters()` / `typesenseCollectionSchema()`). Running it
as-is against a real server fails:

1. **Import fails**: `In ApiCall.php line 414: Parameter \`fields\` is required.`
   The engine's `getOrCreateCollectionFromModel()` tries to create the
   collection with only a `name` (empty schema) → Typesense 400.
2. **Search fails**: `Typesense\Exceptions\RequestMalformed: No search fields specified for the query.`
   `buildSearchParameters()` sends `query_by` = `''` → Typesense 400.

### With the missing wiring supplied **temporarily**

The spike temporarily added `model-settings` for `App\Producers`, `App\Magazine`
and `App\SearchMetric` (mirroring exactly what the models already declare in
`typesenseQueryBy()` / `getCollectionSchema()`), validated everything, then
**reverted** the file (tree left pristine). Results:

- **Import works**: `producers_index` 382, `magazines_index` 1498 (all
  `toSearchableArray` fields indexed — `titles/url/count/established/favorites/mal_id`
  for producers, `name/count/mal_id` for magazines).
- **Native-engine search works end-to-end through REST**: all queries in §5
  returned HTTP 200 with correct totals and ordering.
- **Chunked-pagination path (>250) WORKS** — this is the `jikanPaginate()`
  fix in `app/Mixins/ScoutBuilderMixin.php`. With `q=*`:
  - `magazines_index` (1498 docs): `found` split into six 250-hit pages,
    merged, then paginated client-side. `pagination.items.total` = **1498 as a
    clean int**, `last_visible_page` = 60; page 50 and page 60 (last, 23 items)
    correct; pages 10→11 (chunk boundary at 250) have no overlap.
  - `producers_index` (382 docs): two 250-hit pages merged, total = **382 (int)**,
    16 pages, page 16 = 7 items.
  - The old `array_merge_recursive` bug (which turned `found`/nested hits into
    arrays and corrupted `getTotalCount()`) is gone — the mixin now returns
    `['hits' => $hits, 'found' => (int) $found]` and totals are correct ints.

### Ancillary observation

With `q=*` queries, `DefaultSearchAnalyticsService::logSearch()` runs
`SearchMetric::search('*')` → `take(1)`, which the wildcard query matches
**any** metric row, so multiple broad-content queries keep overwriting one
"popular" row. Pre-existing behaviour unrelated to the pagination fix, but
surfaced by this spike. (Spike-created `search_metrics` rows were deleted
afterwards.)

### Recommended (production) fix — NOT part of this spike

Wire the native-engine config for every searchable model:

```php
// config/scout.php — under 'typesense'
'model-settings' => [
    \App\Producers::class => [
        'search-parameters' => ['query_by' => 'url,titles'],
        'collection-schema' => [ /* Producers::getCollectionSchema() */ ],
    ],
    \App\Magazine::class => [
        'search-parameters' => ['query_by' => 'name'],
        'collection-schema' => [ /* Magazine::getCollectionSchema() */ ],
    ],
    \App\SearchMetric::class => [
        'search-parameters' => ['query_by' => 'search_term'],
        'collection-schema' => [ /* SearchMetric::getCollectionSchema() */ ],
    ],
    // ... Anime, Manga, Person, Character, Club ...
],
```

or provide `typesenseSearchParameters()` / `typesenseCollectionSchema()`
methods on the models and keep the collections pre-created.

### Resolution (implemented) — the two blockers are fixed at the source

`app/JikanApiSearchableModel.php` now implements the native-engine model
contract:
- `typesenseSearchParameters(): array` → returns `query_by` (from each model's
  existing `typesenseQueryBy()`), plus `query_by_weights`/`sort_by` when the
  legacy per-model hooks return values (native `field:direction` format).
- `typesenseCollectionSchema(): array` → returns the model's `getCollectionSchema()`.

So `SCOUT_DRIVER=typesense` works out of the box **without** the
`model-settings` config workaround (the option remains available only if a
per-class override is ever needed). Re-verify with the §4/§5 commands against a
running Typesense server.

---
## 7. Cleanup

```bash
kill $(lsof -tiTCP:8002 -sTCP:LISTEN)   # stop dev server
kill $(lsof -tiTCP:8108 -sTCP:LISTEN)   # stop typesense
rm -rf /tmp/typesense-data              # wipe temp data
# docker: docker compose -f docker-compose.typesense.yml down -v
# remove any spike-created Mongo search_metrics rows:
#   mongosh --eval 'db.getSiblingDB("jikan").search_metrics.deleteMany({search_term:{$in:["studio","pierrot","shonen","*"]}})'
```

Verify no local env/config leftovers: `SCOUT_DRIVER=typesense` must not remain
in `.env`, and `config/scout.php` must be byte-identical to HEAD.

---

## 8. E2E re-verified against Docker Typesense (2026-10-12)

**Environment:** Docker Typesense `27.0` via Colima on `127.0.0.1:8108`
(key `abcd`); native app (Homebrew PHP 8.4) with the §4 env overrides
(`SCOUT_DRIVER=typesense`, `TYPESENSE_*`). **No `model-settings` config, no
pre-created collections** — the Resolution above is the only wiring.

**Import (out of the box):**
- `artisan scout:import "App\Producers"` → `producers_index` auto-created from
  `Producers::typesenseCollectionSchema()` = **382 docs** (fields: `.*`,
  `titles`, `url`, `count`, `established`, `favorites`, `mal_id`).
- `artisan scout:import "App\Magazine"` → `magazines_index` = **1498 docs**.
- (anime/manga/characters/people = 0 docs — upstream parser blockers, see
  `UPSTREAM_ISSUES.md`; the search pipeline itself is model-agnostic.)

**§5 matrix — all pass against the Docker server:**

| Check | Result |
|---|---|
| `producers?q=studio` | 200, total **35** |
| `producers?q=pierrot` | 200, total **1** (Studio Pierrot) |
| `magazines?q=shonen` | 200, total **39** |
| `producers?q=*&order_by=favorites&sort=desc` | 200, top `mal_id` **2** (Kyoto Animation) |
| `magazines?q=*` (chunked >250) | 200, total **1498** (int), **60** pages |
| `producers?q=*` (chunked >250) | 200, total **382** (int), **16** pages |
| boundary `magazines?q=*&page=10`/`page=11` | no id overlap |
| `anime?q=gate` (empty index) | 200, graceful empty |

Chunked-pagination totals (`jikanPaginate()`, `ScoutBuilderMixin`) are clean
ints with no overlap at the 250-hit boundary — consistent with §6's findings.

**Cleanup applied:** dev server stopped; the 4 spike-created
`search_metrics` rows deleted from Mongo. Docker Typesense left running for
future search work.