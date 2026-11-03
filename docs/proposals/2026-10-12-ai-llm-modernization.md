# Proposal — Jikan-rest: modern, AI/LLM-friendly project (2026-10-12)

**Status:** open — 2026-10-12, planning-only (no code changes yet; execution gated on a later go-ahead).
**Decisions:** declares **11 open questions** — 10 resolved, 1 open (Q3, route modernization, explained in §9), see Decisions §10.
**Inputs reviewed:** (a) our own in-repo audit, (b) `Downloads/jikan_rest_ai_modernization_plan.md`
and `Downloads/plan.md` — two external LLM-modernization audits for jikan-rest, reviewed 2026-10-12.
**Owned by:** Irfan Dahir (this document). Execution gated on approval; single branch `laravel-12` (no-PR-split).

---

## 1. Summary

jikan-rest already ships an unusually machine-friendly core: a regenerable OpenAPI spec served to a
self-hosted Scalar UI (`/v4/docs`, `/v4/docs/openapi`), an LLM-friendly docs corpus with a providers
matrix, and a `.backend-suite/` quality context — plus every operation now carries curated
summary/description after the docblock→attribute migration. What it lacks is the **agent/machine
surface the ecosystem now expects**: a root `AGENTS.md`/`CLAUDE.md`, an `llms.txt` index, token-
efficient response options, a function-calling manifest / MCP adapter, optional semantic retrieval,
scraper-rate-limit resilience for agent loops, and CI gates that keep the machine artifacts from
rotting. This proposal integrates our plan with the two external audits into one staged, low-risk
roadmap that **builds on** what exists and keeps v4 compatible throughout.

## 2. Motivation / goals

- **Agents hit the repo and API correctly on the first try** (`AGENTS.md`/`llms.txt`/manifest/MCP).
- **Fewer tokens & round-trips** for LLM consumers (fields/batch/cache/compact formats).
- **Better retrieval** (stronger lexical search now; semantic only as an optional, disableable add-on).
- **Consistent entities & relationships** (identity, aliases, source-vs-derived, data quality).
- **Agent loops don't break during MAL rate-limit/outage** (circuit breaker + safe fallbacks).
- **Machine artifacts can't rot** (CI regenerates + diffs spec/manifest/llms.txt; contract tests).
- **Deterministic by default** — no LLM/GPU/vector DB in the required path; keep the core API
  usable without any AI infrastructure.

## 3. What we take from the external audits

| External idea | Take it? | Where it lands here |
|---|---|---|
| First-party **MCP adapter** (thin, read-only, strict schemas, bounded results, CI-tested tools) | ✅ (strengthens our optional WS) | WS-E |
| **Function-calling manifest** endpoint (`/v4/ai/manifest.json`, OpenAI/Anthropic-style schemas) | ✅ cheap, distinct from MCP | WS-E |
| **Content negotiation** `Accept: text/markdown` + token-optimized cards | ✅ opt-in, measure-first | WS-G |
| **Response ergonomics**: `fields` selection, bounded batch reads, ETag/conditional, stable paging | ✅ | WS-G |
| **Retrieval**: exact-ID/title priority, alias/typo tolerance, curated search benchmark; semantic as **optional** hybrid with async embeddings, timeouts/fallbacks, disableable | ✅ | WS-H |
| **Entity identity & relationships**: alias normalization, canonical refs, depth-limited traversal, source-vs-derived labeling, data-quality checks | ✅ | WS-I |
| **Scraper resilience**: Redis circuit breaker for 429/503 + agent-safe snapshot fallback + retry headers | ✅ (extends our failovers/heartbeat) | WS-J |
| **Observability/evaluation**: cache hit-rate, response bytes/tokens, search latency/lag, per-endpoint latency+errors, agent-task benchmark | ✅ | WS-K |
| **pgvector/PostgreSQL migration** | ❌ keep Mongo+Typesense (our WS-G footprint) | — |
| **Routing every request through an LLM / mandatory embeddings / graph DB** | ❌ (explicit non-goals, §7) | — |

## 4. Current-state audit

### Strengths (already built)
Contract spec + Scalar docs (101 ops, prose-QA'd) · docs corpus (18 files) · `.backend-suite` context
+ audits · cross-platform installer + script wrappers + `.ai/skills` · suite 292/2707 green ·
hermetic test DB · jikan v5 adopted (parser blockers cleared) · Docker validated locally.

### Gaps the plan targets
| # | Gap | Consequence |
|---|---|---|
| G1 | No agent entrypoint (`AGENTS.md`/`CLAUDE.md`) | agents learn the repo ad hoc |
| G2 | No `llms.txt` / no served `/llms.txt` | LLMs lack a deterministic index |
| G3 | Docs lack a single index/quickstart | guessing which doc answers which question |
| G4 | 103 `Controller@action` string routes | indirection, weaker agent grep-ability |
| G5 | PHPStan parked at level 6 | weaker whole-program typing signal |
| G6 | Machine artifacts not CI-guarded | spec/llms/catalog can drift from source |
| G7 | No `docs/proposals/` (now added) | proposal process had no home (fixed by this doc) |
| G8 | No tool manifest or MCP adapter | agents must hand-write HTTP wrappers |
| G9 | No token-efficient response options (`fields`, batch, markdown, ETag) | LLM consumers fetch ~2.5KB+ and repeat calls |
| G10 | Search = Typesense only; no semantic option; no benchmark | natural-language discovery unserved; no measured relevance |
| G11 | Entity identity/alias/derived-data conventions informal | inconsistent cross-resource refs |
| G12 | Scraper rate-limit handling basic (failovers heartbeat only) | agent loops break on 429/Cloudflare; no safe snapshot fallback |
| G13 | No agent-era metrics/benchmark (bytes/tokens, calls-per-task) | nothing measures "fewer calls/smaller responses" |

## 5. Workstreams

> **Decisions applied (2026-10-12):** AGENTS.md + CLAUDE.md include (WS-A); `llms.txt` committed **and** served (WS-A); generated artifacts committed as canonical + CI diff-guarded (WS-D); `/v4/ai/manifest.json` **and** a product MCP adapter (WS-E); the agents-add-an-endpoint eval is acceptance (WS-F); `fields` + bounded batch first, markdown deferred (WS-G); lexical benchmark first, semantic deferred (WS-H); scraper circuit breaker + fallbacks with Redis (WS-J); alias normalization + DQ checks (WS-I). **Planning-only for now — nothing executes until separately approved.**

### WS-A — Agent & machine entrypoints (quick win)
Add root **`AGENTS.md`** (+ thin `CLAUDE.md` include) with the run/test/lint/phpstan/pint/
generate/parity/audit commands, doc map, "add-an-endpoint" recipe, conventions (incl. the
enum-value-docblock gotcha), and `script/` wrappers. Add **`llms.txt`** (+ generated
**`llms-full.txt`**) and a served **`GET /llms.txt`** route.
*Done:* a fresh agent can act correctly in one read; `curl /llms.txt` 200; claude-md-drift resolves.

### WS-B — Docs that compose (quick win)
`docs/INDEX.md` (question→file table) + `docs/AGENTS.md` quickstart; register roles in
`.backend-suite/docs.manifest.json`; keep `docs/proposals/` live (proposal-status audit active).

### WS-C — Modern code surface
Convert the 103 string-action routes to direct class references (option; audit config update),
resume the **PHPStan L7 tunnel**, apply PHP 8.4 idioms where safe, drop dead env vars/config refs.
*Done:* routes explicit (if chosen), phpstan L7 green (or shrinking baseline), no dead env refs,
suite green.

### WS-D — Machine-artifact freshness in CI
A **`composer endpoints:*`** catalog command (operationId/path/summary from routes+OA) feeding
parity, `llms.txt`, and a doc-staleness check; CI regenerates spec + manifests and **fails on git
diff**, runs the `backend-suite` audits + `resolve.py`. *Done:* stale artifacts fail CI.

### WS-E — Agent interfaces (manifest + MCP)
1. **`GET /v4/ai/manifest.json`** — auto-generated function-calling schemas (OpenAI/Anthropic),
   domain-annotated params, strict inputs, bounded result counts. ✓ cheap, no new deps.
2. **First-party MCP adapter** — thin companion (sidecar/package), read-only over existing
   services: initial tools `search_anime`, `get_anime(id, include)`, `get_anime_relations(id)`,
   `search_manga`, `search_characters`, `get_seasonal_anime(year, season)`, `compare_anime(ids, fields)`.
   Strict schemas, output-size caps, timeouts/caching consistent with the public API, actionable
   validation errors, CI test for tool behavior + schema. *Done:* manifest serves; MCP client can
   search+retrieve via documented tools; docs for supported clients.

### WS-F — Developer-env agents
Keep `.ai/skills`; add the **"add-an-endpoint recipe"** doc (route→DTO→handler→service→model→
resource→`@OA`→test→generate→audit) usable verbatim; acceptance = two LLMs complete the recipe.

### WS-G — Response efficiency & token economy
`fields` selection; **bounded batch reads** (multiple IDs in one request); **ETag/conditional
requests** + standardized cache headers; **`Accept: text/markdown`** opt-in compact cards
(measure-first: bytes/tokens before/after); document rate limits/caching/freshness/retry.
*Done:* measured ≤ baseline bytes per endpoint; params remain consistent across the surface.

### WS-H — Retrieval quality
**Lexical first** (Typesense): exact-ID/exact-title priority, alias-aware + typo-tolerant matching,
review searchable fields (titles/alternatives/synopses/genres), stable ranking, curated query
benchmark. **Optional semantic retrieval** (WS-H2): async embeddings from descriptive fields,
hybrid lexical+semantic, timeouts/fallbacks, independently disableable; no mandatory vector infra.
*Done:* search benchmark exists; semantic (if adopted) is opt-in + measured.

### WS-I — Entity identity & relationships
Canonical identifiers + alias normalization, cross-resource refs audit, depth-limited relationship
traversal, **source-vs-derived labeling** (no AI summaries masquerading as facts), data-quality
checks (broken refs/duplicates). *Done:* identity conventions documented + DQ checks in CI.

### WS-J — Scraper resilience for agents
Redis **circuit breaker** on MAL 429/503 + **agent-safe fallback payloads** (cached snapshot with
`is_cached` + `retry_after_seconds`), observability of rate events; extends our failovers/heartbeat.
*Done:* agent loops survive MAL rate-limit windows with explicit retry semantics.

### WS-K — Observability & evaluation
Per-endpoint latency/error rates, cache hit-rate, response bytes/tokens, search latency/lag, MCP
tool metrics; a **version-controlled agent-task benchmark** (lookup/title/typo/filter/natural-
language/similarity/traversal/recovery) measuring relevance, p50/p95 latency, bytes/tokens,
calls-per-task, cost. *Done:* benchmark tracked in-repo + numbers reported.

## 6. Sequencing & effort

| Phase | Workstreams | Effort | Depends on |
|---|---|---|---|
| 1 — Entrypoints (now) | WS-A, WS-B | ~0.5 day | none (runs parallel to full index) |
| 2 — Agent interfaces + freshness | WS-E1 (manifest), WS-D, WS-F | ~1–1.5 days | phase 1; CI runner for green gate (local: same commands) |
| 3 — Response efficiency | WS-G (fields/batch/ETag/markdown) | ~1 day | phase 2; measure-first |
| 4 — Retrieval | WS-H lexical tuning; semantic only if approved | ~1 day (lexical) / 2–3 (semantic) | post-index Typesense re-import; benchmark gate |
| 5 — Core/identity/resilience | WS-C routes+L7, WS-I, WS-J | 2–4 days | parity freeze (routes); Redis decision |
| 6 — MCP + eval | WS-E2 MCP; WS-K benchmark/metrics | ~1–2 days (prototype) | product-value decision; live data |

**Execution status (live):** **Phases 1–2 landed on `laravel-12` (2026-10-12)** — AGENTS.md/CLAUDE.md,
llms.txt + `GET /llms.txt`, `docs/INDEX.md`, `composer endpoints:write|check`,
`GET /v4/ai/manifest.json`, CI artifact-freshness job, add-an-endpoint recipe
(see PR_CHANGELOG §28). Phases 3–6 are tracked in **issue #615**.

## 6.1 Detailed execution plan (planning-only; ordered, each task verifiable)

Hard gates across phases: single branch `laravel-12`; v4 JSON unchanged for default
requests; `l5-swagger:generate` exit 0; suite 292/2707 green; endpoint-parity +
route-coverage + toolchain-gates + proposal-status audits exit 0; artifacts committed
canonical and CI diff-guarded (WS-D). "Measure-first": no new serialization/format ships
a token/bytes delta. Q3 (routes) remains open — not executed until decided.

### Phase 1 — Agent & machine entrypoints (WS-A, WS-B)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 1.1 | Write root **`AGENTS.md`**: what the project is; canonical commands (`tools/dev/php`/`PHP_BIN`): `artisan test`/`test:integration`, `pint`, `phpstan`, `l5-swagger:generate`, `tools/parity/…`, `backend-suite audit …`; doc map (§1.3); conventions incl. spatie-enum venerated docblocks; add-an-endpoint recipe pointer; "must stay green" gates. | `AGENTS.md` (new) | A fresh CLI agent (Claude Code + one other) can boot+test the repo from this file alone (WS-F acceptance). |
| 1.2 | Add thin **`CLAUDE.md`** that points at/embeds `AGENTS.md`. | `CLAUDE.md` (new) | `backend-suite audit claude-md-drift` runs — confirm not red or documented N/A. |
| 1.3 | **`llms.txt`** (root): index → docs tree, `/v4/docs/openapi`, PROVIDERS, PR_CHANGELOG, RFC, proposals; link `llms-full.txt` when generated (1.4/WS-D). | `llms.txt` (new) | `curl` from repo; deterministic content. |
| 1.4 | **`GET /llms.txt`** route (outside throttled v4 group) serving the same content; `text/plain; charset=utf-8`. | `routes/api.php`, `config` none, view/controller (fn) | `curl /llms.txt` 200; no `Jikan` beat/limits on it; parity `get_docs` unaffected. |
| 1.5 | **`docs/INDEX.md`** (question→file) + link from README quick-start; cross-link from `AGENTS.md`. | `docs/INDEX.md` (new); `README.MD` | All links resolve (relative). |
| 1.6 | Phase-record: changelog entry; manifests/roles unchanged (INDEX is not a suite role). | `docs/PR_CHANGELOG.md` | `resolve.py` still exit 0; tree clean. |

### Phase 2 — Agent interfaces + freshness (WS-E1, WS-D, WS-F)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 2.1 | **`composer endpoints:*`** console command: walk routes + `@OA` (operationId/path/summary/tags) → emit `tools/parity/endpoints.txt`-style catalog + JSON; make extraction deterministic. | `app/Console/Commands/` (new), composer `scripts` | Command idempotent; catalog == spec ops (101). |
| 2.2 | **`/v4/ai/manifest.json`**: OpenAI-tools + Anthropic-tools shapes generated from the catalog; strict inputs (types, enums, bounds, `required`), bounded `max` counts; annotated param docs. Exposed in spec (`@OA`) + docs. | new command+service+controller; `@OA`; docs | Manifest validates; `generate` deterministic (`git diff` clean). |
| 2.3 | **CI freshness**: add to `ci.yml` — after deps: regenerate spec + manifest + llms-full, `git diff --exit-code`; run `backend-suite` audits + `resolve.py`. (Runner-gated execute; local run now.) | `.github/workflows/ci.yml` | Local regen-diff clean; CI steps syntactically valid (re-run static validation). |
| 2.4 | **Add-an-endpoint recipe** doc (route→DTO→handler→service→model→resource→`@OA`→test→generate→parity→audit). | `docs/RECIPE_ADD_ENDPOINT.md` (new) | Two LLMs complete the recipe end-to-end (Q6 acceptance). |

### Phase 3 — Response efficiency (WS-G)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 3.1 | **`fields=a,b`** selection on high-volume GETs (anime/manga details + search): DTO/validator + resource projection; `@OA` parameter; default = current shape. | controllers/handlers/resources/DTOs | Default responses byte-identical (parity); `fields` tests pass. |
| 3.2 | **Bounded batch reads** (`?ids=` up to N=50) for anime/manga/characters: handler + resource + bounds/errors; `@OA`. | new handlers/routes/resources | 1+50 ids returns compact map; 51 → 422; parity of single-id default intact. |
| 3.3 | **ETag/conditional + cache headers**: per-endpoint TTL→`Cache-Control: max-age`; ETag from cache key; `If-None-Match` → 304. | middleware/response macros, config | 304 path tested; headers documented; parity unaffected. |
| 3.4 | Measure bytes/tokens before→after (small script + committed numbers in changelog). | `tools/` metric script; changelog | Delta recorded (target ≠0). |

### Phase 4 — Retrieval (WS-H)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 4.1 | Typesense tuning: exact-ID/exact-title priority, alias-aware (`titles` incl. synonyms/romanized), typo tolerance, stable ranking; validate via current integration run (post-index data). | search config/contract, `JikanApiSearchableModel` weights; `@OA` | Search benchmark starts reflecting gains. |
| 4.2 | **Curated search benchmark**: queries (ID lookup, exact title, alias/typo, filters, natural-language) + expected results; data-driven test/harness; recorded scores. | `tools/` + tests data | Benchmark committed; runs in CI. |
| 4.3 | Semantic (deferred per Q9): explicitly NOT built this phase; log evidence when benchmark exists. | — | Decision log marks revisit gate. |

### Phase 5 — Core surface + identity + resilience (WS-C?, WS-I, WS-J)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 5.1 | Route modernization (Q3): IF approved, convert 103 string actions → `[Controller::class, 'method']`; update `.backend-suite/arch-rules.json` (`actionPattern`/`controllerDirs`) + audits. | `routes/*.php`, arch-rules | route-coverage + endpoint-parity green; parity unchanged. |
| 5.2 | **PHPStan L7 tunnel**: bump level; add-fix findings; regenerate baseline incrementally until exit 0. | `phpstan.neon`, baselines, app fixes | exit 0; no new ignores without reason. |
| 5.3 | **Entity identity/DQ**: alias normalization rules + data-quality checks (broken refs, duplicates, alias consistency) as tests/CI job; source-vs-derived labeling documented. | tests/CI + docs | DQ job green (or reports known counts). |
| 5.4 | **Scraper breaker + fallbacks**: Redis circuit breaker on MAL 429/503 + fallback payload (`is_cached`, `retry_after_seconds`) + `@OA` + docs; extends failovers/heartbeat. | service/middleware/config-redis/docs | Unit tests for breaker+fallback; parity shape for cached snapshot documented. |

### Phase 6 — MCP + evaluation (WS-E2, WS-K)
| ID | Task | Touch | Verify / done |
|---|---|---|---|
| 6.1 | **MCP adapter** (product per Q4): thin companion package exposing read-only tools (`search_anime`, `get_anime`, `get_anime_relations`, `search_manga`, `search_characters`, `get_seasonal_anime`, `compare_anime`), strict schemas, output caps, timeouts/caching consistent with the API; CI tool tests + docs. | new package/module (separate), CI, docs | MCP client (e.g., Claude Desktop) searches+retrieves via documented tools; tool tests pass. |
| 6.2 | **Observability**: per-endpoint latency/errors, cache hit-rate, response bytes, search lag, MCP call metrics. | metrics/log channels/CI | Metrics visible; no sensitive content logged. |
| 6.3 | **Agent-task benchmark harness**: calls-per-task, bytes/tokens, p50/p95 latency; baseline committed; re-run at milestones. | `tools/` harness + data | Baseline published in changelog. |

**Sequencing rules:** phases 1–2 land without waiting on the index/runner (docs/config/commands only). Phase 3+ depend on post-index data for real measurements; 5.1 gated on Q3; 5.2, 5.4 are long but independent. Each phase ends with: changelog entry, docs refresh, PR-body milestone note, tree clean.

## 7. Guardrails (adopted from the external audits)

- No routing every request through an LLM; no mandatory vector DB; no GPU/local model required.
- Embed only fields worth embedding (never IDs/scores); embeddings async, version-tracked, disableable.
- No breaking v4 to clean schemas; never claim inferred similarity/popularity = critical acclaim.
- Response efficiency always opt-in/measure-first; bounded batch/expansion; public errors stay
  actionable without stack traces/secrets.

## 8. Risks & decisions

- Scope creep toward "AI product" — mitigated by guardrails + phasing (deterministic first).
- Route modernization is the only behavior-touching change — behind parity/audit freeze.
- MCP/manifest add surface — manifest ships first (cheap); MCP stays a prototype until value proven.
- Semantic search needs an embedding source decision (managed API vs local model vs none).
- Keep Mongo+Typesense footprint (external audit agrees); no graph DB.

## 9. Open questions

1. Which agent-file naming do we standardize on — `AGENTS.md` (ecosystem) with a `CLAUDE.md` include, or only `CLAUDE.md`? **→ Decided: AGENTS.md + CLAUDE.md include.**
2. Serve `/llms.txt` on the public API, or keep it repo-only initially? **→ Decided: both (committed root file + served route).**
3. Is the 103-route `Controller@action` → class-ref modernization in scope, and before or after the full-index parity freeze? **→ OPEN.** (What it is: routes declared as `'uses' => 'AnimeController@main'` become explicit `[AnimeController::class, 'main']` references — clearer/greppable/static, but a wide behaviour-neutral change touching what route-coverage/endpoint-parity read. Default interim: defer until after the parity freeze, make it optional.)
4. Agent-interface ordering: `/v4/ai/manifest.json` first + MCP companion second — and is MCP a product deliverable or a dev/prototype? **→ Decided: both ship as product deliverables.**
5. How much generated content (`llms-full.txt`, endpoint catalog, manifest) do we commit vs generate in CI? **→ Decided: commit canonical artifacts; CI regenerates and fails on drift.**
6. Do we pursue the formal "agents add an endpoint" eval (two LLMs, one recipe) as the acceptance test for WS-A/WS-F? **→ Decided: yes, it is the acceptance test.**
7. Response-efficiency scope: which of `fields` / batch reads / ETag+conditional / compact representations ship first, and what are the default size bounds? **→ Decided: `fields` + bounded batch reads first; ETag/cache headers next; defaults bounded and measured.**
8. Markdown content negotiation (`Accept: text/markdown`): opt-in `?format=` too, which endpoints, and the cache/header semantics? **→ Decided: defer; measure fields/batch token gains first.**
9. Semantic retrieval: adopt as optional? engine/model/hosting choice, hybrid+disableable, gated on the search benchmark? **→ Decided: decision deferred; lexical benchmark first, revisit with evidence.**
10. Scraper resilience: circuit breaker + agent-safe fallback payload schema — accept the contract/header implications, and Redis as the breaker store? **→ Decided: breaker + fallbacks via Redis.**
11. Entity identity & data-quality pipeline scope: alias normalization + source-vs-derived labeling — how deep in phase 5? **→ Decided: alias normalization + DQ checks (medium scope).**

## Decisions

Resolved 10/11 (2026-10-12). One remains open (Q3).

### ✅ #1 — Agent-file naming (`AGENTS.md` vs `CLAUDE.md`)
Resolved: **AGENTS.md + CLAUDE.md include** (WS-A). 2026-10-12.

### ✅ #2 — Serve `/llms.txt` publicly vs repo-only
Resolved: **both — committed root file + served route** (WS-A). 2026-10-12.

### 🔴 #3 — Route modernization scope/timing
Open — pending the explanation + decision (see §9 Q3). Default interim: defer until after the parity freeze; optional.

### ✅ #4 — Agent-interface ordering + MCP product vs prototype
Resolved: **manifest AND MCP both ship as product deliverables** (WS-E). 2026-10-12.

### ✅ #5 — Commit vs CI-generate machine artifacts
Resolved: **commit canonical; CI regenerates + fails on drift** (WS-D). 2026-10-12.

### ✅ #6 — "Agents add an endpoint" eval as acceptance
Resolved: **yes — the acceptance test for WS-A/WS-F**. 2026-10-12.

### ✅ #7 — Response-efficiency scope (`fields`/batch/ETag) + size bounds
Resolved: **`fields` + bounded batch first; ETag/cache headers next; bounded + measured** (WS-G). 2026-10-12.

### ✅ #8 — Markdown content negotiation scope
Resolved: **defer; measure fields/batch token gains first** (WS-G). 2026-10-12.

### ✅ #9 — Semantic retrieval: optional adoption + engine + benchmark gate
Resolved: **decision deferred until the lexical benchmark exists** (WS-H). 2026-10-12.

### ✅ #10 — Scraper circuit breaker + fallback payload contract
Resolved: **breaker + agent-safe fallbacks via Redis** (WS-J). 2026-10-12.

### ✅ #11 — Entity identity & data-quality pipeline scope
Resolved: **alias normalization + DQ checks, medium scope** (WS-I). 2026-10-12.

_Last updated: 2026-10-12_