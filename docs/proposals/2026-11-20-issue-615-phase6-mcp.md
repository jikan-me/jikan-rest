# Issue #615 — phase 6 MCP + evaluation plan

Status: **slices 1 and 2 implemented; observability and the benchmark are
planned**. Branch `stack/615-06-mcp-evaluation`, based on
`stack/615-05-identity-resilience`.

Implemented in slice 1: `laravel/mcp` production dependency, `POST /mcp` via
`App\Mcp\Servers\JikanServer`, `MCP_API_KEY` bearer auth
(`App\Http\Middleware\AuthenticateMcp`, fail closed), and the first read-only
tools.

Implemented in slice 2: all seven read-only tools (`search_anime`, `get_anime`,
`get_anime_relations`, `search_manga`, `search_characters`,
`get_seasonal_anime`, `compare_anime`), sharing bounded card shaping
(`App\Mcp\Support\CompactCard`) and the v4 kernel adapter. See `docs/MCP.md`
for the operator guide.

Everything below about observability and the benchmark is still a proposal —
describe it as planned, never as shipped behaviour.

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

All seven tools are implemented on this branch. All are read-only, return
validation errors for bad input, and bound their output (cards, per-relation
entry caps, 2-5 ids for comparisons).

## Implementation slices

1. **MCP foundation — implemented (slice 1)**
   - Add `laravel/mcp` as a production dependency (`^1.2`).
   - Register `/mcp` HTTP endpoint (`routes/ai.php`, `App\Mcp\Servers\JikanServer`).
   - Add `MCP_API_KEY` middleware; missing/empty key or wrong token → 401
     (`App\Http\Middleware\AuthenticateMcp`, constant-time compare, fail closed).
   - Add a health/introspection test (401 paths, initialize + `tools/list`,
     invalid input, missing ids).

2. **Tools — implemented (slice 2)**
   - Shipped: `search_anime`, `get_anime`, `get_anime_relations`,
     `search_manga`, `search_characters`, `get_seasonal_anime` and
     `compare_anime`. All dispatch an internal `/v4` request through the HTTP
     kernel instead of duplicating controller logic.
   - Output is bounded: at most 25 cards per search/season, 5 entries per
     comparison, and 50 related entries per relation group (with a truncation
     flag). `compare_anime` only exposes compact-card top-level fields and
     ignores unknown field names.
   - Tests cover success and error paths for every tool, bound violations and
     unknown ids.

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
