# MCP server (read-only, opt-in)

Jikan exposes a small read-only [Model Context Protocol](https://modelcontextprotocol.io)
server at `POST /mcp`, built on [`laravel/mcp`](https://laravel.com/docs/mcp).
It wraps the existing v4 API; it does **not** add a new data source, an LLM, or
write access.

The endpoint is opt-in and fails closed: with no `MCP_API_KEY` configured it is
unusable and every request is rejected with `401`.

## Endpoint and auth

| | |
|---|---|
| URL | `POST /mcp` (HTTP, JSON-RPC 2.0; authenticated `GET`/`DELETE` answer `405`) |
| Auth | `Authorization: Bearer <MCP_API_KEY>` |
| Config | `MCP_API_KEY` → `config('mcp.api_key')` (`config/mcp.php`) |
| Failure mode | Missing key, empty key or wrong token → `401 Unauthorized` |

The comparison is constant-time (`hash_equals`) and the middleware is applied
to every method registered for `/mcp`, so there is no unauthenticated path.
`WWW-Authenticate: Bearer realm="mcp"` is returned with `401`.

The v4 API itself still has no auth; MCP auth is only about controlling access
to this adapter.

## Tools

| Tool | Input | Output |
|---|---|---|
| `search_anime` | `query` (1-100 chars), `limit` (1-25, default 10), optional `type`, `status`, `min_score` | `{count, has_next_page, items[]}` — compact anime cards |
| `get_anime` | `mal_id` (integer ≥ 1) | the same `data` object as `GET /v4/anime/{mal_id}` |
| `search_manga` | `query` (1-100 chars), `limit` (1-25, default 10), optional `type`, `status`, `min_score` | `{count, has_next_page, items[]}` — compact manga cards |
| `search_characters` | `query` (1-100 chars), `limit` (1-25, default 10) | `{count, has_next_page, items[]}` — compact character cards |
| `get_anime_relations` | `mal_id` (integer ≥ 1) | `{data[], truncated}` — one item per relation; each `entry` list is capped at 50, with `entry_total` and `truncated` per relation |
| `get_seasonal_anime` | `year` (1000-2999), `season` (`winter`/`spring`/`summer`/`fall`), `limit` (1-25, default 10) | `{count, has_next_page, items[]}` — compact anime cards |
| `compare_anime` | `ids` (2-5 distinct integers ≥ 1), optional `fields` (1-20 compact-card field names) | `{count, items[]}` — one card per id, in the requested order |

- Cards keep a bounded field set per media type:
  - anime: `mal_id`, `url`, `images`, titles, `type`, `episodes`, `status`,
    `score`, `rank`, `members`, season/year, …
  - manga: `mal_id`, `url`, `images`, titles, `type`, `chapters`, `volumes`,
    `status`, `score`, `rank`, `members`, …
  - character: `mal_id`, `name`, `name_kanji`, `nicknames`, `favorites`, `url`.
  Heavy fields such as `synopsis`, `background` and `about` are left out. At
  most 25 cards per call (except `compare_anime`, which is capped at 5).
- `compare_anime` only accepts compact-card field names for `fields`; unknown
  names are ignored rather than rejected, and `mal_id` is always kept so cards
  stay identifiable.
- Every tool returns MCP structured content (`result.structuredContent`).
- Invalid input is a typed tool error (`result.isError: true` with a message),
  not a PHP exception or a 5xx.
- An unknown `mal_id` (or an unknown id in `compare_anime`) is a tool error,
  mirroring the v4 `404`/missing entry.
- All tools are annotated `readOnlyHint` / `idempotentHint` in `tools/list`.

## How it is implemented

`App\Mcp\Support\V4ApiClient` dispatches an internal `GET /v4/...` request
through the HTTP kernel (`Request::create()` + `Illuminate\Contracts\Http\Kernel`),
so the tools reuse the v4 controllers, validation, caching and search
degradation instead of duplicating them. The outer MCP request is restored after
each tool call.

Files:

- `routes/ai.php` — registers `Mcp::web('/mcp', JikanServer::class)` behind the
  bearer guard.
- `app/Mcp/Servers/JikanServer.php` — server metadata and tool registry.
- `app/Mcp/Tools/` — `SearchAnimeTool`, `GetAnimeTool`, `SearchMangaTool`,
  `SearchCharactersTool`, `GetAnimeRelationsTool`, `GetSeasonalAnimeTool`,
  `CompareAnimeTool`.
- `app/Mcp/Support/CompactCard.php` — shared bounded card shaping.
- `app/Mcp/Support/V4ApiClient.php` — internal v4 adapter.
- `app/Http/Middleware/AuthenticateMcp.php` — bearer auth.
- `config/mcp.php` — `api_key` (package defaults are merged in by
  `laravel/mcp`).

## Local usage

```bash
# 1. Configure a token (empty/absent = endpoint disabled)
echo 'MCP_API_KEY='"$(openssl rand -hex 32)" >> .env

# 2. Start the app
php artisan serve

# 3. Handshake (MCP 2025-06-18 style)
curl -s http://127.0.0.1:8000/mcp \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{"protocolVersion":"2025-06-18","capabilities":{},"clientInfo":{"name":"curl","version":"1"}}}'

# 4. List tools
curl -s http://127.0.0.1:8000/mcp \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"jsonrpc":"2.0","id":2,"method":"tools/list","params":{}}'

# 5. Call a tool
curl -s http://127.0.0.1:8000/mcp \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"jsonrpc":"2.0","id":3,"method":"tools/call","params":{"name":"get_anime","arguments":{"mal_id":1}}}'

# 6. Compare two anime with a reduced card shape
curl -s http://127.0.0.1:8000/mcp \
  -H 'Authorization: Bearer <token>' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"jsonrpc":"2.0","id":4,"method":"tools/call","params":{"name":"compare_anime","arguments":{"ids":[1,5114],"fields":["title","score","episodes"]}}}'
```

Tests:

```bash
vendor/bin/phpunit --no-coverage --filter='Mcp'
```

The integration tests cover `401` without a token / with a wrong token / with no
configured key, a full `initialize` + `tools/list` handshake (all seven tools),
a happy path for every tool, bound violations (bad `limit`, too few
`compare_anime` ids, relations entry cap) and unknown ids returning tool
errors.

## Scope notes

- Read-only: no write tools, no LLM in the request path, no new outbound
  service.
- Responses are bounded per call; there is no unbounded catalog dump.
- There is no dedicated `/mcp` rate limiter in this slice. If the endpoint is
  exposed publicly, terminate TLS and apply rate limits at the proxy (or add a
  `throttle:mcp` route middleware later).
- The endpoint is not part of the v4 OpenAPI contract; it is an operator
  opt-in adapter. Still planned: observability (tool-call metrics) and the
  agent-task benchmark — see
  `docs/proposals/2026-11-20-issue-615-phase6-mcp.md`.
