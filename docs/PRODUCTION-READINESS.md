# jikan-rest — Production Readiness

The internal checklist for whether this is fit to put in front of real users. Gates,
their current state, and what is blocking each.

**This is the internal version.** It names branches, files, internal tool names and
commit hashes, none of which belong in front of a client. The client-facing summary
is a *derived collateral* generated from this document — never authored separately,
or the two will diverge and someone will read the wrong one.

Owned by **Irfan Dahir**.

---

## 1. How to read this

| State | Means |
|---|---|
| ✅ | Done and verified. Say how it was verified |
| 🟡 | In progress, owner named, no unknowns |
| 🔴 | Blocked or not started |
| ⚪ | Not applicable to this project — say why |

**"Verified" means someone checked the real thing.** A passing test suite is not
verification that a user-facing flow works, and neither is "should work".

## 2. Gates

| # | Gate | State | Owner | Blocking |
|---|---|---|---|---|
| 1 | Error and offline behaviour | ✅ | Irfan | Redacted v4 error envelope `{status,type,message}`; unhandled → generic envelope; standard 404 shape (incl. empty-data random endpoints); `/up` liveness 200 |
| 2 | Config-cache safety / boot | ✅ | Irfan | `config:cache` green; boots fully from cached config (`env()` outside config removed) |
| 3 | Security hardening | ✅ | Irfan | CORS restricted; cache-bypass token; rate limiting active; `APP_DEBUG=false` in prod; no secrets in source; `.env.dist` sane defaults |
| 4 | Rate limiting | ✅ | Irfan | `throttle:v4` per-IP on public routes; audit `rate-limit` clean |
| 5 | Data handling & privacy | ✅ | Irfan | Read-only public MAL data, no PII collection/retention; search metrics internal (Mongo); Sentry optional (default off, 0.1 sample) |
| 6 | Performance | ✅ | Irfan | Octane/RoadRunner workers; lazy handler resolution; chunked Typesense pagination verified >250 hits; soak: 12 iterations × 10 endpoints, byte-identical, zero worker restarts |
| 7 | Test & quality suite | ✅ | Irfan | 292 tests / 2707 assertions green; parity harness zero new shape/status diffs; PHPStan level 6 baseline 0; Pint scoped; backend-suite audits clean (residuals documented) |
| 8 | Search (Typesense) | 🟡 | Irfan | E2E verified locally against a real server (import, search, chunked pagination). Prod search blocked on anime/manga **data** (upstream parser #598–602) + a real Typesense key/collections |
| 9 | Docker / ops | 🟡 | Irfan | Image builds + full compose stack healthy (validated locally, Colima/arm64); **CI first-green needs a GitHub runner**; rollback = replace stateless image |
| 10 | Third-party licence compliance | 🟡 | Irfan | Verify jikan-me/jikan licence terms (scraping/redistributing MAL data) + dependency stack incl. the Goutte shim before public relaunch |
| 11 | On-call / support path | 🟡 | Irfan | Upstream OSS (no SLA); Discord channel; `/up` available for LB probes |
| 12 | Auth and session handling | ⚪ | — | Read-only public JSON API — deliberately no auth, no sessions, no CSRF |
| 13 | Accessibility | ⚪ | — | No UI — JSON API only |

## 3. What we need to ship

| Needed | Why | Needed by | If late |
|---|---|---|---|
| Real GitHub runner | CI first-green (PHP 8.3 matrix + Mongo service + Docker build) | Relaunch | CI ships unproven (logic verified locally only) |
| Anime/manga data + parser fix (upstream jikan #598–602) | Detail endpoints return 200; search indexes populated | Relaunch | Endpoints 500 (documented upstream blocker) |
| Typesense API key + prod collections | Production search | When search ships | `SCOUT_DRIVER=null` fallback (search off) |
| Container registry / built image | Container distribution | Relaunch | Build on the runner |

## 4. Known risks accepted

| Risk | Impact | Accepted by | Date |
|---|---|---|---|
| Detail endpoints gated on anime/manga **data** (parser is fixed in jikan v5; schedule index + full indexers populate) | Data-gated 4xx-able surface until indexed | Irfan | 2026-10-12 |
| ~~Goutte shim~~ — **resolved**: jikan v5.0.0-beta drops Goutte; shim + replace removed 2026-10-12 | — | — | — |
| Typesense-only search (Elasticsearch path removed) | Single engine dependency | Irfan (decision) | 2026-10-10 |
| Typesense image pinned `27.0` + no-curl healthcheck | Upgrade path minor | Irfan | 2026-10-12 |

---

_Last updated: 2026-10-12_