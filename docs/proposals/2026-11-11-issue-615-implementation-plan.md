# Issue #615 — AI/LLM modernization phases 3–6 (implementation plan)

Status: implementation started on `stack/615-03-response-efficiency`, based on
`laravel-12`. This document records the decisions reached in the requirements
grilling session and the delivery stack.

## Settled decisions

- **Stack:** four stacked branches off `laravel-12`:
  `615-03-response-efficiency` → `615-04-retrieval` →
  `615-05-identity-resilience` → `615-06-mcp-evaluation`.
- **Contract:** v4 default responses stay byte-identical; every new input is
  opt-in.
- **`fields`:** top-level allowlist only; unknown fields are a `400`.
- **`ids`:** bounded to 50, comma-separated, existing items only, request order
  preserved, response is `{"data": [...]}` with no pagination.
- **ETag:** weak ETag derived from the semantic response body; matching
  `If-None-Match` returns `304`.
- **Coverage:** `fields` on every v4 JSON response with a `data` payload;
  `ids` on the anime/manga/character search endpoints.
- **PHPStan:** level 7 with a committed baseline; no new findings.
- **Identity/DQ:** alias normalization + DQ command/CI + source-vs-derived docs.
- **Scraper resilience:** circuit breaker via the Laravel cache abstraction;
  fallback payloads (`is_cached`, `retry_after_seconds`) on anime, manga,
  characters, and people.
- **MCP:** in-repo Laravel MCP server, HTTP `/mcp`, `MCP_API_KEY` bearer auth.
- **Observability:** JSON logs in Docker; `/metrics` off by default with a bearer
  token.
- **Search benchmark:** committed fixtures in CI; full-data Typesense run
  locally.
- **Route modernization:** already complete — `routes/web.v4.php` is class-based;
  Q3 can be closed.

## Phase 3 — response efficiency (current stack)

1. `fields=` middleware + allowlist config (`config/response_fields.php`).
2. `?ids=` batch lookup in the search handlers, bounded at 50.
3. Weak ETag / `Cache-Control` middleware and `304` path.
4. Response-size measurement script and committed before/after numbers.

## Phase 4 — retrieval

Typesense ranking tuning (exact ID/title, aliases, typo tolerance, stable order),
curated benchmark fixtures, and a CI-runnable benchmark. Semantic retrieval is
explicitly deferred.

## Phase 5 — identity and resilience

Alias normalization; data-quality command and CI gate (new errors fail, known
baseline reports); source-vs-derived documentation; cache-backed MAL circuit
breaker with fallback payloads. PHPStan L7 baseline.

## Phase 6 — MCP and evaluation

In-repo read-only MCP tools over HTTP; JSON/Prometheus observability; agent-task
benchmark harness (calls, bytes/tokens, p50/p95).

## Gates

- PHPUnit, PHPStan, Pint, `composer endpoints:check`, generated-artifact diff.
- Route coverage / endpoint parity / toolchain audits.
- Default v4 response parity for every opt-in feature.
