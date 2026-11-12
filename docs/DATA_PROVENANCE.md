# Data provenance and labeling

Jikan REST stores data with different levels of trust. Consumers and agents
should be able to tell what is canonical, what is derived, and what is merely
cached.

## Classes

| Class | Meaning | Examples |
|---|---|---|
| **Canonical** | Source data obtained from MyAnimeList and keyed by MAL ids | anime/manga records, characters, people, producers, clubs, magazines |
| **Derived** | Rebuildable output produced from canonical data | Typesense indexes, normalized aliases, search rankings, cached response envelopes |
| **Cached** | A previous response or scraper result kept for latency/rate-limit reasons | Mongo scraper records, negative-cache entries, stale fallbacks |
| **Inferred** | Generated interpretation that is not a MAL fact | future AI summaries, semantic scores |

## Rules

- Canonical data is identified by MAL id and must not be overwritten by derived
  or inferred data.
- Derived data is rebuildable from canonical data; deleting an index or alias
  field must never change the canonical record.
- Cached data must expose that it was cached when it is served as a fallback.
- Inferred data must never be presented as a MAL fact.
- The v4 public contract is unchanged; provenance metadata belongs in headers
  or opt-in fields, not in default response shapes.

## Current implementation

- Canonical: Mongo collections populated by `indexer:*`.
- Derived: Typesense collections, transformed titles/synonyms.
- Cached: `CachedData`, scraper cache, negative cache, fallback responses.
- Inferred: none in the current API.
