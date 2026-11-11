# Search ranking audit — phase 4

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

1. `_text_match`
2. `mal_id`

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

## What is not proven yet

- Full-data ranking numbers on the 3,021-anime index.
- Manga/character/person/producer/club/user ranking fixtures.
- Numeric-title vs numeric-ID collisions.
- Typo-tolerance tuning beyond the existing short-query behavior.
- Existing Typesense indexes need a reindex to gain `mal_id_text`.
