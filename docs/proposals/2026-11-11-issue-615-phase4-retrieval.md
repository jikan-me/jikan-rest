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
- Local full-data run uses Docker Typesense; current subset is 3,021 anime,
  full 10k run is recorded once available.
- Index schema changes require an explicit reindex and clear stale-index errors.

## Delivered so far

- `tools/search-benchmark/queries.json` (exact ID/title, alias, typo, CJK,
  filter, no-hit).
- `tools/search-benchmark/benchmark.php` with P@1/P@5/MRR/latency and `--check`.
- `php artisan search:benchmark`.
- `docs/SEARCH_BENCHMARK.md`.
- `mal_id_text` indexing and high query weight across user-searchable models.
- `title_synonyms_transformed` for anime/manga.
- deterministic `mal_id:asc` tie-breaker.
- `prioritize_exact_match`.
- `SearchIndexFieldsTest` and `SearchRankingTest`.
- `search-benchmark` CI job with Typesense 27.0.

## Remaining

- Run the ranking test against a live Typesense service in CI.
- Capture baseline vs after numbers on the 3,021-anime index.
- Re-run full 10k benchmark when data is available.
- Update changelog and PR description.
