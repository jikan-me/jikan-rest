# Issue #615 — phase 6 MCP + evaluation plan

Status: planning only, branch `stack/615-06-mcp-evaluation` based on
`stack/615-05-identity-resilience`.

## Scope

Deliver a read-only MCP adapter over the existing v4 API, plus observability and
an agent-task benchmark. No LLM in the request path; no response-contract change.

## Decisions

- **Delivery:** in-repo Laravel MCP server, not a sidecar.
- **Transport:** HTTP at `/mcp`.
- **Auth:** static bearer token from `MCP_API_KEY`.
- **Tools:** read-only, bounded, strict schemas.
- **Output:** tool response caps; no unbounded catalog dumps.
- **Timeouts:** align with `SOURCE_TIMEOUT` and search cutoff.
- **Caching:** reuse existing HTTP/cache behavior; no separate MCP cache layer.

## Tools

| Tool | Input | Output |
|---|---|---|
| `search_anime` | query + filters + limit | compact anime cards |
| `get_anime` | mal_id + optional fields | anime detail |
| `get_anime_relations` | mal_id | related entries |
| `search_manga` | query + filters + limit | compact manga cards |
| `search_characters` | query + limit | character cards |
| `get_seasonal_anime` | year + season + limit | seasonal cards |
| `compare_anime` | ids + fields | compact comparison |

All tools are read-only and return validation errors for bad input.

## Implementation slices

1. **MCP foundation**
   - Add `laravel/mcp` as a production dependency.
   - Register `/mcp` HTTP endpoint.
   - Add `MCP_API_KEY` middleware; missing/wrong token → 401.
   - Add a health/introspection test.

2. **Tools**
   - Implement tool classes with strict input schemas and output caps.
   - Reuse controllers/services/DTOs; do not duplicate API logic.
   - Add per-tool tests for success, empty result, invalid input, upstream failure.

3. **Observability**
   - JSON log format in containers (already planned in phase 6 scope).
   - Metrics: tool calls, latency, errors, search latency, cache status.
   - `/metrics` off by default with bearer token.
   - No secrets or raw queries in logs.

4. **Agent-task benchmark**
   - Versioned tasks: lookup, title search, typo, filter, seasonal, relations,
     comparison, recovery.
   - Metrics: calls/task, bytes/tokens, p50/p95, success rate.
   - Baseline committed; rerun at milestones.

## Acceptance criteria

- `/mcp` requires a valid `MCP_API_KEY`.
- Every tool has strict input validation and bounded output.
- Tool results are byte-compatible with the underlying v4 data where applicable.
- Unreachable upstream/search degrades without 500 where the API already does.
- Metrics endpoint disabled by default; enabled with token.
- Benchmark baseline committed and CI-runnable.
- Full suite, PHPStan, Pint, artifact freshness green.

## Risks

- MCP dependency/version drift.
- Output caps hiding required fields.
- Token leakage in logs.
- Metrics cardinality explosion from query strings.
- Tool duplication of controller logic.

## Out of scope

- LLM-generated answers or summaries.
- Write tools.
- Semantic/vector search.
- Multi-tenant auth.
