# Issue #615 — phase 4 retrieval implementation plan

Status: implementation started on `stack/615-04-retrieval`, stacked on
`stack/615-03-response-efficiency`.

## Decisions

- Lexical retrieval only; semantic/vector work is deferred.
- All user-searchable models: anime, manga, characters, people, producers,
  clubs, magazines, users.
- Exact MAL IDs are searchable through a `mal_id_text` string field with high
  query weight; numeric queries still return normal result sets.
- Exact-title ranking uses Typesense `prioritize_exact_match`.
- Anime/manga aliases are indexed as transformed synonyms.
- Deterministic final ordering uses `mal_id:asc`.
- CI uses Typesense 27.0 with synthetic fixtures.
- Gate: P@1 >= 0.90, MRR >= 0.85, exact-ID/exact-title fixtures must rank first.
- Latency is reported, not gated.
- Local full-data run uses Docker Typesense; the 2026-11-20 run on 29,735
  anime is recorded in `docs/SEARCH_BENCHMARK.md`.
- Index schema changes require an explicit reindex and clear stale-index errors.

## Delivered so far

- `tools/search-benchmark/queries.json` (exact ID/title, alias, typo, CJK,
  filter, no-hit).
- `tools/search-benchmark/benchmark.php` with P@1/P@5/MRR/latency and `--check`.
- `php artisan search:benchmark`.
- `php artisan search:doctor` for schema and index/database drift checks.
- Full-data baseline recorded on 29,735 anime (2026-11-20).
- `docs/SEARCH_BENCHMARK.md`.
- `mal_id_text` indexing and high query weight across user-searchable models.
- `title_synonyms_transformed` for anime/manga.
- deterministic `mal_id:asc` tie-breaker.
- `prioritize_exact_match`.
- `SearchIndexFieldsTest` and `SearchRankingTest`.
- `search-benchmark` CI job: Typesense 27.0 plus the `SearchRankingTest`
  fixture gate. The benchmark harness itself is local/opt-in.

## Remaining

- Ranking fixtures for manga, producers, clubs, magazines and users.
- Typo-tolerance tuning beyond the existing short-query behavior.
- Update changelog and PR description.
