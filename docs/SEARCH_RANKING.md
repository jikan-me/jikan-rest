# Search ranking audit — phase 4

Ranking changes in this phase affect relevance only. Response shapes,
pagination envelopes, and error contracts are unchanged.

## Before phase 4

Anime and manga sorted by three fields:

1. `_text_match(buckets:N)` — text relevance
2. `popularity` — ascending (lower MAL popularity rank first)
3. `rank` — ascending

Other searchable models had no explicit sort configuration and inherited
Typesense's relevance-first default.

## After phase 4

Anime and manga sort by:

1. `_text_match(buckets:N)` — text relevance
2. `popularity` — ascending
3. `mal_id` — ascending, deterministic tie-break

`rank` is no longer a sort field because Typesense accepts at most **three**
`sort_by` fields. Popularity is deliberately kept as the second field.

Other user-searchable models use:

| Model | Second sort field |
|---|---|
| Characters | `member_favorites` descending |
| People | `member_favorites` descending |
| Producers | `favorites` descending |
| Clubs | `members` descending |
| Magazines | `count` descending |
| Users (`Profile`) | none — inherits the base two-field sort |

Anime, manga, characters, people, producers, clubs, and magazines all end with
`mal_id:asc` as the deterministic third field. Users (`Profile`) do not override
`getSearchIndexSortBy()`, so they use the base sort:
`_text_match(buckets:N):desc, mal_id:asc`.

## Exact-ID behavior

`mal_id_text` is indexed with weight 10. For non-numeric queries it does not
match, so it has no effect on normal title relevance. For numeric queries it
lets an exact MAL ID outrank title-only matches.

Numeric collision policy: when the query is numeric and an exact title exists,
the ID field is removed for that request so the exact title wins. Without an
exact title, the exact ID is boosted. This keeps human title searches and
agent ID lookups useful without one silently overriding the other.

## Exact-title behavior

`prioritize_exact_match` is enabled for Typesense searches. Exact title matches
receive the boost before popularity is used as a tie-breaker.

## Short queries

Queries of three characters or fewer use the existing short-query override
(`_text_match`, `mal_id`) so tiny queries remain deterministic. Numeric IDs are
still boosted by `mal_id_text` unless an exact title takes precedence.

## Reindexing for `mal_id_text`

`mal_id_text` is part of the collection schema for all eight searchable models:
Anime, Manga, Character, Person, Producers, Club, Magazine, and Profile. A
collection created before phase 4 does not have the field, and Scout does not
alter an existing collection schema. Recreate and re-import each collection:

```bash
php artisan scout:import "App\Anime" --fresh
php artisan scout:import "App\Manga" --fresh
php artisan scout:import "App\Character" --fresh
php artisan scout:import "App\Person" --fresh
php artisan scout:import "App\Producers" --fresh
php artisan scout:import "App\Club" --fresh
php artisan scout:import "App\Magazine" --fresh
php artisan scout:import "App\Profile" --fresh
```

`--fresh` deletes the collection first, so the new schema (including
`mal_id_text`) is created on import. Expect numeric-ID queries to rank
unboosted on any collection that has not been re-imported yet.

## Operating the search index

`php artisan search:doctor [--json]` checks every searchable collection for the
phase-4 schema and for drift. For each model it reports the collection name,
whether `mal_id_text` is present, stored (Mongo) vs indexed (Typesense) document
counts, and an issue. It exits non-zero when a collection is unavailable, is
missing `mal_id_text`, or its indexed count differs from Mongo.

`php artisan search:benchmark [options]` measures P@1/P@5/MRR and p50/p95
latency against a running API instance:

| Option | Meaning |
|---|---|
| `--base-url` | API base URL (default `http://127.0.0.1:8000`) |
| `--queries` | Corpus path (default `tools/search-benchmark/queries.json`) |
| `--check` | Exit non-zero on relevance regression |
| `--json` | Machine-readable output |
| `--when-ready` | Skip until the anime count reaches `--threshold` |
| `--threshold` | Minimum anime count for `--when-ready` (default `10000`) |

The gate fails when `P@1 < 0.90`, `MRR < 0.85`, or an exact-ID/exact-title
fixture is not at rank 1. Latency is reported, not gated. See
[SEARCH_BENCHMARK.md](SEARCH_BENCHMARK.md) for the corpus and the recorded
full-data run.

## What is not proven yet

- Ranking fixtures for manga, producers, clubs, magazines, and users.
  Characters and people have a popularity tie-break test; anime has the full
  benchmark corpus.
- Typo tolerance beyond the existing short-query behavior.
