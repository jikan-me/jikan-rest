# Post-full-index verification runbook

> For use after the full indexing run completes (`indexer:anime` → `indexer:manga`,
> launched 2026-10-12, ~30,393 anime + a manga id set back-to-back against the app
> on `:8000`). Turn the finished index into *verified* state without scrambling.
> Context: jikan v5.0.0-beta fixes the parser blockers (`docs/UPSTREAM_ISSUES.md`);
> the data this run produces is the first complete dataset since the migration.

## 0. Confirm completion

```bash
tail -5 /tmp/indexer-anime.log /tmp/indexer-manga.log   # "Indexing complete" on both
pgrep -f "indexer:"                                     # no survivors
tools/dev/php -r 'require "vendor/autoload.php"; $d=(new MongoDB\Client("mongodb://localhost:27017"))->selectDatabase("jikan"); foreach(["anime","manga"] as $c) printf("%s=%d\n", $c, $d->selectCollection($c)->countDocuments());'
```

### 0.5 Populate characters & people

Characters/people are populated **relationally** — no stale external id cache.
Phase A harvests live ids from the anime/manga we already store; Phase B scrapes
each id's detail doc:

```bash
# Phase A — harvest ids from stored anime/manga relations (anime characters+staff,
# manga characters+authors) into storage/app/indexer/harvest_{character,people}.json
tools/dev/php artisan indexer:harvest --resume --delay=2

# Phase B — scrape each harvested id into the characters/people collections
tools/dev/php artisan indexer:items character --source=harvest --resume --delay=2
tools/dev/php artisan indexer:items people    --source=harvest --resume --delay=2
```

`indexer:items anime|manga` still uses the purarue caches (those are current);
`character|people` default to `--source=harvest` (`--source=purarue` forces the
legacy caches). All commands support `--resume`/`--failed`/`--reverse`/`--index`/
`--limit`. Clubs have no id cache (lazy scraping via user club lists). See
`docs/INDEXER_SCALING.md`.

After Mongo is populated, import to Typesense for search:
`scout:import "App\Character"` / `"App\Person"`.

## 1. Drain failures + prune

The indexers record 429/error ids to `storage/app/indexer/indexer_{anime,manga}.failed`.
Retry until the failed file is empty/small, then prune removed entries:

```bash
tools/dev/php artisan indexer:anime --failed --delay=2
tools/dev/php artisan indexer:manga --failed --delay=2
tools/dev/php artisan indexer:anime-sweep      # deletes anime no longer on MAL
tools/dev/php artisan indexer:manga-sweep
```

## 2. Parity re-verify + ratchet

The committed baseline (`tools/parity/baseline`) was ratcheted on 2026-10-12 to the
schedule-subset state. With a full dataset the previously-empty endpoints
(manga, characters, people, deeper anime sub-resources, top/seasons) now return
data → expect **positive diffs**, then ratchet:

```bash
tools/dev/php artisan serve --host=127.0.0.1 --port=8000 &   # or reuse existing :8000
tools/dev/php tools/parity/snapshot.php --base-url=http://127.0.0.1:8000 --out=/tmp/parity_candidate
tools/dev/php tools/parity/compare.php --baseline=tools/parity/baseline --candidate=/tmp/parity_candidate
```

- Review the diff list: status diffs allowed = `/docs` 301 (intentional) and
  endpoints that were 404/empty that now return 200 (the point of the run).
- Ratchet when clean of regressions: `rm -rf tools/parity/baseline && cp -r /tmp/parity_candidate tools/parity/baseline`
- Re-verify stability: snapshot again → compare vs the new baseline → expect
  `OK: N endpoints — shapes and statuses match`. Commit the baseline with a note
  (old one is recoverable from git).

## 3. Search re-import (Typesense)

With anime/manga/character/person data now indexed, populate the search engine
(server on `127.0.0.1:8108`, key `abcd` — `docker compose -f docker-compose.typesense.yml up -d`):

```bash
export SCOUT_DRIVER=typesense TYPESENSE_API_KEY=abcd TYPESENSE_HOST=localhost TYPESENSE_PORT=8108
for m in Anime Manga Character Person Club GenreAnime GenreManga Profile; do
  tools/dev/php artisan scout:import "App\\$m"
done
curl -sS http://localhost:8108/collections -H "X-TYPESENSE-API-KEY: abcd"   # counts
curl "http://127.0.0.1:8000/v4/anime?q=one%20piece"                          # spot-check
```

See `docs/TYPESENSE_SPIKE.md` §8 for the verification matrix (producers/
magazines already verified; repeat the sort + chunked >250 checks).

## 4. Endpoint smoke matrix (expect 200)

`/v4/top/anime` · `/v4/top/manga` · `/v4/seasons/now` · `/v4/seasons/upcoming` ·
`/v4/schedules` · `/v4/anime/{id}` · `/v4/anime/{id}/full` · `/v4/manga/{id}` ·
`/v4/characters/{id}` · `/v4/people/{id}` · `/v4/random/anime` ·
`/v4/users/recentlyonline` · search `?q=` variants.

## 5. Container demo (optional)

The Docker compose stack has its own empty Mongo. To demo the image serving real
data, point the compose `app` at a DB containing the index (e.g. sync volumes /
run the scheduler inside the container with `JIKAN_ENABLE_PERIODICAL_FULL_INDEXER=1`,
or `exec` an index run). Otherwise the empty-DB `ParserException`/404 is expected.

## 6. Hygiene + record

- Delete test `search_metrics` rows created by broad `q=` checks
  (`db.search_metrics.deleteMany({search_term: {$in: ["*", ...]}})`).
- Verify indexes survived the write load:
  `db.anime.getIndexes()` → `schedule`, `season`, `top_airing`, `top_upcoming`
  (see `docs/DB_INDEXES.md`); text `search` indexes per collection.
- Heartbeat: `/` + `/v4` `parser_version` should read **v5.0.0-beta.2**.
- Update the living records: parity counts + this batch in `docs/PR_CHANGELOG.md`,
  `docs/UPSTREAM_PR.md` §1 Verification, `docs/PRODUCTION-READINESS.md` gate rows,
  then push + refresh the PR description numbers.

---

_Last updated: 2026-10-12_