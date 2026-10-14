# Populating characters & people — relational scraping (2026-10-13)

How bulk-populating the non-anime collections works, the measured costs, and how
to run it safely. Companion to `docs/POST_FULL_INDEX.md`.

## 0. Why not the purarue caches

`purarue/mal-id-cache` supplies usable, current id sets for **anime/manga**, but
its **character/people** caches are stale and full of dead ids — scraping them
yields a large share of 404s. The reliable source of character/person ids is our
**own stored anime/manga**, which reference them live.

## 1. Two-phase relational indexer

**Phase A — `indexer:harvest`** walks the stored `anime`/`manga` collections and
harvests ids from the per-entry sub-resources:

| Source | Gives | Cost |
|---|---|---|
| `manga.authors` (stored) | mangaka → **people** ids | zero (already in Mongo) |
| `anime/{id}/characters` | **character** ids + voice-actor **people** ids | 1 scrape × anime |
| `anime/{id}/staff` | crew/director **people** ids (unless `--no-staff`) | 1 scrape × anime |
| `manga/{id}/characters` | **character** ids | 1 scrape × manga |

Ids are deduped into `storage/app/indexer/harvest_character.json` and
`harvest_people.json` (cumulative + re-runnable; `--resume`/`--failed`/`--index`/
`--limit`/`--reverse`, `--kind=anime|manga|all`).

**Phase B — `indexer:items character|people --source=harvest`** reads those id
files and scrapes each detail doc through our own endpoint:

```
id → /v4/characters/{id}  → ItemLookupHandler → CachedScraperService::find
     → cache lookup in Mongo → else scrape MAL → parse (jikan v5) → insert
```

Result: the **MongoDB document store** (`characters`, `people`). It does **not**
populate the Typesense search index — that is a separate
`scout:import "App\Character"` / `"App\Person"` step (search is graceful-empty
until then). `indexer:items anime|manga` keeps using purarue; `character|people`
default to `--source=harvest` (`--source=purarue` forces the legacy caches).

## 2. Measured costs (2026-10-13, this machine)

| Family | id source size | cold per-id | warm/cached | steady-state @ `--delay=2` |
|---|---|---|---|---|
| character | measured | ~0.7 s | ~14 ms | **~19 s/entry** incl. `/staff` pass |
| people | measured | ~0.7 s | ~15 ms | ~19 s/entry |

- **Measured (2026-10-14, mid-run, cold pages + machine contention):** the early
  ~4 s/entry sample undercounted steady state ≈ **19 s/entry** ⇒ Phase A for
  10,399 anime ≈ **~2 days**.
- **Harvested set sizes (grew far faster than planned):** at 4.7k anime ≈ 45%:
  **60,259 characters / 25,354 people**. Final projection ≈ **~100k+ characters /
  40–55k people** ⇒ Phase B is a **multi-day** detail scrape of that set — plan
  `--delay=2` cadence + `--failed` sweeps (runbook below).
- Smoke test: 4 source docs (2 anime + 2 manga) harvested 396 characters / 723
  people, 0 failures.
- MAL page timeout: `SOURCE_TIMEOUT` (default 10 s).

## 3. Failure handling

- **429 / timeouts / parser edge** → recorded to `.failed`; re-run with `--failed`
  after a cool-down.
- **Resume:** `--resume` continues from the saved index.
- **Strict `$fillable` gaps** surfaced at insert: `App\Anime`/`App\Manga`
  (`external_links`/`streaming_links`) and `App\Person`
  (`alternate_names`/`external_links`) — fixed. Keep `PHP_MEMORY_LIMIT=512M`.
- **Scout/Typesense:** `toSearchableArray()` is now null-safe on missing
  `aired`/`published` (was a latent "array offset on null" whenever a doc without
  air dates was indexed).

## 4. Running at scale (options)

1. **Sequential, slow and safe (recommended):**
   ```bash
   nohup tools/dev/php artisan indexer:harvest --resume --delay=2 > /tmp/harvest.log 2>&1 &
   # then, once Phase A is done:
   nohup tools/dev/php artisan indexer:items character --source=harvest --resume --delay=2 > /tmp/i-char.log 2>&1 &
   nohup tools/dev/php artisan indexer:items people    --source=harvest --resume --delay=2 > /tmp/i-people.log 2>&1 &
   ```
2. **Faster (modest parallelism):** split each phase by `--index` ranges and run
   2–4 processes with `--delay=3` each; recover 429s via `--failed` later.
3. **Tune down delay** (`--delay=1`) — faster, more 429s, recoverable.
4. Long-term (future, issue #615): queue-backed workers calling the parser
   directly instead of HTTP self-scrape.

## 5. What gets populated (vs lazy)

- **Populated by the indexers:** the main family doc (Mongo).
- **Stays lazy (scrape-on-miss):** sub-resources (`/voices`, `/anime`, `/manga`,
  `/pictures`, …) cache on first request.
- **Search:** after Mongo population, `scout:import "App\Character"` / `"App\Person"`.

## 6. Moving the run to another machine (handoff)

The whole run is reconstructable from three artifacts + the branch:

1. `mongodump --db jikan` — the doc store (`anime`/`manga`) Phase A reads its id
   list from, plus everything else.
2. `storage/app/indexer/*` — harvest id sets + progress (`*.save`) + failure
   (`*.failed`) files. The harvest JSONs are **cumulative and dedup-based**;
   resuming re-walks from the saved index and merges. (When stopping mid-run,
   rewind the `indexer_harvest_anime.save` number by ~100 to cover entries whose
   ids were still in memory at the last flush.)
3. Typesense data — portable, but cheapest to rebuild: restore Mongo, start
   Typesense (native 27.0 binary or `docker-compose.typesense.yml`), then
   `scout:import "App\Anime" "App\Manga"` (≈ seconds).

```bash
# new machine
./install.sh --yes && mongorestore --db jikan <dump>/jikan
tar -xzf indexer-state.tgz -C .            # -> storage/app/indexer/*
tools/dev/php artisan indexer:harvest --resume --delay=2
```

## 7. Production hardening noticed

`config/jikan.php` `rate_limit_per_minute` **defaults to 0 (unlimited)** — the
`throttle:v4` middleware exists but the limit is off unless set. Set
`RATE_LIMIT_PER_MINUTE` (e.g. `240`) in deploy so the public API is rate-limited.

---
_Last updated: 2026-10-13_
