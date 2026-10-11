# RFC-0001 — Adopt Laravel 13 (migrate from Lumen 9)

| | |
|---|---|
| Status | **Accepted** (recorded after the change was executed — see Decision) |
| Author(s) | Irfan Dahir |
| Reviewers | Upstream maintainers via jikan-rest draft PR <#614> |
| Date opened | 2026-10-09 |
| Date decided | 2026-10-11 |
| Supersedes / superseded by | — |

A proposal, not a decision — but for the record: this change has already been
executed on branch `laravel-12` (78 commits); this document is the written
proposal + decision trail, and the outcome lives in `docs/MIGRATION_PLAN.md`
and `docs/PR_CHANGELOG.md`.

---

## 1. Summary

Migrate jikan-rest, a read-only public v4 JSON API over MyAnimeList data, from
the archived Lumen 9 micro-framework (Laravel 9 / security-EOL) to **Laravel
13** with first-party packages — `mongodb/laravel-mongodb` v5, Laravel Scout's
**native Typesense engine**, Octane (RoadRunner default), PHPUnit 12,
l5-swagger — while preserving the public v4 JSON contract byte-for-byte,
proven by a committed parity harness.

## 2. Motivation

- Lumen 9 is **archived**; Laravel 9 reached **security end-of-life**.
- The Lumen + Jena/Herd Mongo-driver pairing cannot be maintained on current
  PHP and blocks everything (upstream PR #569 stalled).
- The public contract (106 GET endpoints) must not change — the migration is
  the only safe way forward, and must be provably non-breaking.

## 3. Proposal

- Framework: `laravel/framework ^13` + `mongodb/laravel-mongodb ^5`
  (drop Lumen bootstrap; new `bootstrap/app.php`, routes, base classes).
- Search: Laravel Scout **native** `TypesenseEngine` via model methods
  (`typesenseCollectionSchema()` / `typesenseSearchParameters()`), replacing the
  third-party driver; chunked pagination (`jikanPaginate()`) fixed and
  verified >250 hits.
- Runtime: Octane (RoadRunner default, FrankenPHP opt-in via `OCTANE_SERVER`).
- Docs/tests/tooling: PHPUnit 12, l5-swagger, PHPStan level 6 (baseline),
  Pint, `laravel/boost` + skills, backend-quality-suite context layer.
- Safety net: `tools/parity/` status+shape harness committed before any
  behavior change; `.backend-suite/` audits green/rationed.
- Delivery: squash branch `laravel-12` → 14-PR series to upstream (§5 of
  `docs/UPSTREAM_PR.md`).

## 4. Alternatives considered

- **Stay on Lumen 9**: untenable (archived + EOL, driver lock-in).
- **Laravel 11/12 first, bump later**: adds a second migration pass; Laravel 13
  is chosen directly because the cost from 12 is ~nil and 13 ships Scout's
  native Typesense engine (smaller dependency set).
- **FrankenPHP as the Octane default**: kept opt-in; RoadRunner matches prior
  port layout and is the lower-friction default.

## 5. Drawbacks and risks

- Large surface (755 files / 73+ commits); behavior-drift risk on any route or
  error path → mitigated by the parity harness (zero new shape/status diffs)
  and the seeded-endpoint byte-match set.
- Maintainer churn: upstream is conservative and stuck on the Lumen stack;
  mitigated by the staged, minimal-diff PR series + offer of co-maintenance.
- Typesense-only search removes the Elasticsearch path (upstream may prefer
  keeping it — flagged as an open question for review).

## 6. Impact

| Area | Impact |
|---|---|
| Backward compatibility | None — v4 response shape, routes and query-param contract preserved (parity harness). |
| Existing consumers | None — byte-identical response bodies on seeded endpoints. |
| Build/release process | New — Docker image (PHP 8.4 + RoadRunner), CI matrix written, first-green still runner-gated. |
| Performance | Improved — Octane/RoadRunner workers, lazy handler resolution, chunked search pagination. |
| Security | Improved — config-cache safety, error redaction, rate limiting, cache-bypass token, no secrets in source. |

## 7. Open questions

Resolved during review (2026-10-11/12):

1. Laravel 13 directly vs 12-first → **13 directly** (decision).
2. Octane RoadRunner default → **yes**.
3. Gate the jikan v5 (Goutte-drop) upgrade behind this PR → **no**; keep the
   local Goutte shim until v5 lands upstream.

## 8. Decision

**Accepted.** Proceed with the PR series to `jikan-me/jikan-rest` (draft PR
#614 on the fork; 14-PR split in `docs/UPSTREAM_PR.md` §5). RFC-first filing
skipped by decision on 2026-10-12 — the PR series is the vehicle. Decision
log: `docs/MIGRATION_PLAN.md`, `docs/PR_CHANGELOG.md`.

> Future RFCs (e.g. pagination strategy, rate limiting, versioning) append as
> RFC-0002+ in this file.

---

_Last updated: 2026-10-12_