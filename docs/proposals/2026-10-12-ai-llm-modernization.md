# Proposal — Jikan-rest: modern, AI/LLM-friendly project (2026-10-12)

**Status:** open — 2026-10-12, planning-only (no code changes yet). This proposal declares **6 open questions** (see §7).
**Scope:** audit + plan for making the jikan-rest codebase and docs maximally navigable by
both humans and AI agents (IDE assistants, CLI agents, LLM tooling / MCP).
**Owned by:** Irfan Dahir (this document) — execution gated on approval, single branch `laravel-12`,
consistent with the no-PR-split decision.

---

## 1. Summary

jikan-rest is already unusually well-documented for the parity era: the whole v4 contract is
exposed as a regenerable OpenAPI spec rendered by Scalar (`/v4/docs`, `/v4/docs/openapi`), docs
live under `docs/` with an LLM-friendly providers matrix, and a machine-readable quality context
(`.backend-suite/`) ships. What it lacks are the **agent-facing surfaces** the ecosystem now
expects: a root `AGENTS.md`/`CLAUDE.md`, an `llms.txt` index, a single "how this repo works for
agents" entrypoint, more explicit code routing, and CI gates that keep the machine-readable
artifacts from drifting. This proposal turns those gaps into a staged, low-risk plan that builds
**on** what exists rather than re-inventing it.

## 2. Motivation / goals

- **Onboarding & velocity for AI agents** — an agent hitting the repo today must reverse-engineer
  the run/test/generate/audit loop from `docs/`; a root `AGENTS.md` + `llms.txt` makes the first
  correct action obvious (test → lint → phpstan → generate spec → parity → audits).
- **Machine "grep-ability" of the contract** — `llms.txt` (and a served `/llms.txt`) give LLMs a
  deterministic pointer set: docs tree, spec path, providers matrix, changelog, decisions.
- **Modern code surface** — typed DTOs/enums already exist; removing the last Lumen-era
  indirections (string-action routes, baseline-ridden PHPStan level) makes the code both
  clearer to agents and more static-analysis-friendly.
- **Freshness guarantees** — CI that fails when `llms.txt`/spec/docs go stale, so the AI-facing
  artifacts stay truthful.

## 3. Current-state audit

### Strengths (already built — do not redo)
| Area | State |
|---|---|
| API contract | OpenAPI spec at `/v4/docs/openapi` (regenerable, `artisan l5-swagger:generate` exit 0), 101 ops all summarized+described (prose-QA'd), rendered by self-hosted Scalar at `/v4/docs` |
| Docs corpus | 18 files under `docs/` incl. PROVIDERS (LLM-friendly matrix), ARCHITECTURE, DESIGN, RFC (RFC-0001), PRODUCTION-READINESS, POST_FULL_INDEX, MIGRATION_PLAN, PR_CHANGELOG |
| Quality context | `.backend-suite/` profile + arch-rules + doc-roles manifest (7 mapped/11 declined), 19 audits (13 clean, residual documented) |
| Dev tooling | cross-platform `install.sh` (with `--dry-run`), `script/` wrappers, `laravel/boost` + 4 marketplace skills (`.ai/skills/*`) |
| Testing | 292 / 2707 green, hermetic test DB, engine-less CI env contract |

### Gaps (the plan targets these)
| # | Gap | Evidence | Consequence |
|---|---|---|---|
| G1 | **No agent entrypoint** | `AGENTS.md`/`CLAUDE.md` absent; claude-md-drift audit N/A | agents learn the repo ad hoc; setup/run conventions live only in SETUP.md |
| G2 | **No `llms.txt`** | absent at root; no served route | LLMs lack a deterministic index of repo docs/endpoints |
| G3 | **Docs lack a single index** | 18 files, no index/quickstart-for-agents | humans+agents must guess which doc answers which question |
| G4 | **Route indirection** | 103 `'uses' => 'Controller@action'` strings | controller resolution not greppable/static; agent must chase routes |
| G5 | **PHPStan parked at L6** | baseline-pinned; L7 tunnel parked | weaker whole-program typing signal for agents & tools |
| G6 | **Machine artifacts not CI-guarded** | no check tying spec/llms.txt/endpoint catalog to source | docs/spec can silently drift from code |
| G7 | **No proposals dir** | `docs/proposals/` absent; proposal-status audit inert | our own RFC process has no home in the suite's role map |
| G8 | **Consumer-side AI surface minimal** | only `/v4/docs*`; no `/llms.txt`, no robots/AI hints (`llms.txt` serving is expected for products) | external agents can't self-discover the API cheaply |

## 4. Workstreams

### WS-A — Agent & machine entrypoints (quick win)
Goal: a first-action-obvious repo for AI agents.
- Add **`AGENTS.md`** at repo root (the emerging multi-agent standard; also aliased/pinned by
  `CLAUDE.md` for Claude Code via a small `CLAUDE.md` that includes it). Content: what the project
  is, run/test/lint/phpstan/pint/generate/parity/audit commands (via `tools/dev/php` or `PHP_BIN`),
  doc map, "add-an-endpoint" recipe, quality gates that must stay green, conventions
  (enum values live in venerated docblocks — the gotcha we hit), and the `script/` wrappers.
- Add **`llms.txt`** at root (llmstxt.org convention): a human+LLM-readable index listing the docs
  tree, `/v4/docs/openapi` contract URL, providers matrix, changelog, decisions, and the endpoint
  catalog; plus a longer **`llms-full.txt`** (generated, optional).
- Add a **`GET /llms.txt`** route (outside the throttled v4 group) serving the same content for
  product/consumer agents.
- Done when: `AGENTS.md` present + `script/`-driven quickstart verifiable by a fresh agent in one
  read; `curl /llms.txt` 200; `claude-md-drift` audit runs clean (or is explicitly marked N/A with
  a documented reason).

### WS-B — Docs that compose (quick win)
Goal: one index, no guessing.
- **`docs/INDEX.md`** — one table mapping question → file (setup, run, providers, architecture,
  design, rfc, readiness, post-index, audit, upstream).
- **`docs/AGENTS.md`** — the in-repo agent quickstart (can live as the canonical doc, root file
  includes/symlinks it).
- **Doc-role map**: register `AGENTS.md`/`INDEX.md` in `.backend-suite/docs.manifest.json` as the
  `entry-point`-adjacent roles (and keep `entry-point` → README).
- Add **`docs/proposals/`** for this + future proposals (activating the `proposal-status` audit).
- Done when: a fresh agent can find, in ≤2 reads, how to run tests, add an endpoint, and regenerate
  docs; `proposal-status` audit is no longer inert.

### WS-C — Modern code surface (medium)
Goal: less indirection, stronger static typing.
- **Explicit routing (P2, optional)**: convert 103 `Controller@action` string routes to direct
  class references (`[AnimeController::class, 'main']` or invokable/closure-with-class). Keep
  `route-coverage`/`endpoint-parity` audits satisfied (they read `uses`; update
  `.backend-suite/arch-rules.json` `actionPattern`/`controllerDirs` accordingly). This is the
  largest behavioural-neutral change — sequence last, behind the parity/data freeze.
- **PHPStan L7 tunnel**: resume (it was parked) once the L6 baseline is ratcheted; regenerate the
  baseline against attributes-era code (`app/Compat` entries already removed).
- **API modernization hygiene**: adopt PHP 8.4 idioms where safe (readonly DTOs, native enums where
  already used, property promotion), drop dead env vars (`CACHE_META_EXPIRE`,
  `CACHE_PRODUCERS_EXPIRE`) and dead config references, and keep the v4 JSON keys byte-stable.
- Done when: routes explicit (if chosen), `phpstan` level 7 exits 0 (or a documented, shrinking
  baseline), no dead env references, suite green.

### WS-D — Machine artifact freshness in CI (medium)
Goal: AI-facing artifacts can't rot.
- **`composer endpoints:*` catalog command** (mirrors the upstream jikan convention): derive an
  endpoint index from routes+OA (operationId, path, summary) → used by parity, `llms.txt`, and a
  doc-staleness check.
- **CI additions** (to the validated `ci.yml`): regenerate spec + assert `git diff` clean;
  regenerate `llms-full.txt` + assert clean; run `backend-suite` audits (endpoint-parity,
  route-coverage, toolchain-gates, proposal-status) + `resolve.py` (no new stub debt).
- Done when: a stale spec/llms.txt/proposal status fails CI; parity + audits are in the pipeline.

### WS-E — Consumer/API AI surface (medium, optional)
Goal: product agents can self-discover.
- `/llms.txt` route (WS-A) is the anchor. Optional:
  - **JSON-LD/API hints** in the docs page (`<meta>` describing the OpenAPI URL).
  - **MCP server** (phase 2): a thin `mcp` adapter exposing tools for `search`,
    `anime_lookup`, `parity_check`, `docs_get` against the live API, so external agents can
    operate the product with tools rather than curl-scraping.
  - `robots.txt` remains 404-safe; consider allowing agents (permissive `User-agent: *` + allow).
- Done when: `/llms.txt` serves; optional MCP repo documented and runnable in dev.

### WS-F — Developer-env agents (quick win)
Goal: the human-agent loop is reproducible.
- Keep `.ai/skills` (boost) but add one **"add-an-endpoint recipe"** doc (route → DTO → handler →
  service → model → resource → `@OA` → test → `l5-swagger:generate` → parity → audit), usable
  verbatim by agent or human.
- Document the **canonical agent command set** in `AGENTS.md` (WS-A) — one-liners that never
  require reading a README section meaningfully (they delegate to `script/`).
- Done when: two LLMs (e.g., Claude Code + an OpenCode-style agent) can each add a trivial
  endpoint end-to-end using only the docs.

## 5. Sequencing & effort

| Phase | Workstreams | Effort | Depends on |
|---|---|---|---|
| **1 — Entrypoints (now)** | WS-A (`AGENTS.md`/`CLAUDE.md`), WS-B doc index + `docs/proposals/` | ~0.5 day | none (index running in parallel; no data needed) |
| **2 — Freshness guards** | WS-D catalog command + CI checks + WS-F recipe | ~1 day | phase 1; CI runner for the green gate (local: run the same commands in the docs test) |
| **3 — Code surface** | WS-C explicit routes (optional)+ L7 tunnel + hygiene | 1–2 days | post-index parity freeze (routes are the riskiest change) |
| **4 — Consumer surface** | WS-E `/llms.txt` (phase 1 item), MCP + hints (optional) | ~1 day (MCP: prototype) | approved scope; live API with full data (post-index) |

Quick wins are phases 1–2; they need no data and can land on `laravel-12` immediately.

## 6. Risks & decisions

- **Contract drift**: `llms.txt`/spec/endpoint catalog must never contradict code — mitigated by
  WS-D CI guards + regen-from-source (no hand-maintained copies).
- **Route modernization** (WS-C) is the only behaviour-touching change besides docs; keep it
  explicit-optional, behind the parity harness and the suite audits.
- **MCP server** adds surface — keep it an optional phase-2 prototype (dev-only) until product
  value is proven; does not affect the v4 contract.
- **Scope posture**: everything here stays on `laravel-12` single-PR; docs/tooling additions are
  dev-only-for-maintainers (same stance as the backend-suite integration).
- **Decision needed**: adopt `AGENTS.md` naming vs `CLAUDE.md`; serve `/llms.txt` publicly vs
  repo-only; do the route modernization now or defer to post-merge.

## 7. Open questions

1. Which agent-file naming do we standardize on — `AGENTS.md` (ecosystem) with a `CLAUDE.md`
   include, or only `CLAUDE.md`?
2. Serve `/llms.txt` on the public API, or keep it repo-only initially?
3. Is the 103-route `Controller@action` → class-ref modernization in scope, and if so, before or
   after the full-index parity freeze?
4. Should MCP tooling be a product-facing deliverable (phase 2), or a dev-orchestration prototype?
5. How much generated content (`llms-full.txt`, endpoint catalog) do we commit vs generate in CI?
6. Do we pursue a formal "agents add an endpoint" eval (two LLMs, one recipe) as the acceptance
   test for WS-A/WS-F?

## Decisions

All six questions from §7 are open; each is a decision heading below for the proposal-status
convention (🔴 = open, ✅ = resolved — none resolved yet).

### 🔴 #1 — Agent-file naming (`AGENTS.md` vs `CLAUDE.md`)

Open; see §7 Q1.

### 🔴 #2 — Serve `/llms.txt` publicly vs repo-only

Open; see §7 Q2.

### 🔴 #3 — Route modernization scope/timing

Open; see §7 Q3.

### 🔴 #4 — MCP tooling: product vs dev prototype

Open; see §7 Q4.

### 🔴 #5 — Commit vs CI-generate machine artifacts

Open; see §7 Q5.

### 🔴 #6 — "Agents add an endpoint" eval as acceptance

Open; see §7 Q6.

---

_Last updated: 2026-10-12_