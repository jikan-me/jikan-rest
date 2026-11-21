# Upstream PR / RFC Packaging (living)

> **Purpose.** Turns all the local work on branch `laravel-12` into the artifacts
> needed to contribute back to [jikan-me/jikan-rest](https://github.com/jikan-me/jikan-rest)
> (migration) and [jikan-me/jikan](https://github.com/jikan-me/jikan) (parser fixes).
> Keep this file updated as work progresses — it is the "whenever we push" playbook.
>
> Nothing has been pushed upstream. Everything lives in the local fork.
> Counterpart docs: `PR_CHANGELOG.md` (what was done), `UPSTREAM_ISSUES.md`
> (upstream blockers, parked), `AUDIT.md` / `audit-phase2.md` (findings + status),
> `MIGRATION_PLAN.md` §11 (original strategy).

---

## 1. Status

| Piece | State |
|---|---|
| RFC issue for `jikan-me/jikan-rest` | **Decided against (2026-10-12): no RFC — the PR series is the vehicle.** The §4 draft is kept for reference only; its open questions should be raised in PR discussion |
| PR series | **Delivery stack (updated 2026-11-20).** `laravel-12` is the release line; draft **PR #614** is open against `master`. Issue #615 work merges as an ordered stack: #632 → #633 → #636 → `stack/615-06-mcp-evaluation` (plan only). `fix/616-search-index-drift` (#634) branches from `laravel-12` and merges independently. See §5. Both earlier framings ("14-PR series", then "single PR, no split") are superseded. |
| Infra agent (T1–T4) | T1 heartbeat ✅ · T2 TTL guard ✅ · T3 Dockerfile ✅ · T4 PHPStan/Pint/CI ✅ |
| Local work committed at reconciliation | insights + CORS **removals** (`c858c3d`), **observability** (`41dd860`), `UPSTREAM_PR.md` (`8f94dfa`) |
| Verification | **Snapshot 2026-10-12: 292 tests / 2707 assertions — fully green** (re-run `vendor/bin/phpunit --list-tests` / the suite for the current count; later runs in `docs/PR_CHANGELOG.md`) · PHPStan level 7 (two baselines) exit 0 · **Parity: 59 endpoints match, 0 diffs** (baseline ratcheted 2026-10-12 after **jikan v5 adoption** cleared the parser blockers — anime/manga/characters/people/recentlyonline now serve live data) · Docker build + stack validated locally (Colima) · Typesense E2E verified (Docker server) |
| Docker / CI | Image builds clean on a real runtime; `app`/`mongo`/`redis`/`typesense` healthy, `/up` 200, `rr --version` 2025.1.15 (arm64). CI matrix first-green still parked for a GitHub runner |
| `jikan-me/jikan` parser fixes | **Resolved by adopting jikan v5.0.0-beta** (2026-10-12): UP-01…06 all cleared, verified live; Goutte shim removed |
| Decisions made | Laravel **13** (final), **Typesense-only** search (native Scout engine), Octane RoadRunner default, PHPUnit 12, security/config-cache hardening in scope |

Upstream context from `MIGRATION_PLAN.md` §11: maintainers are **conservative and
currently stuck on the Lumen + Jena/Herd Mongo-driver combination** (PR #569). Plan
for an RFC-first, staged sequence of green PRs, minimal diffs, no unrelated churn,
offer co-maintenance.

---

## 2. What we are proposing, in one paragraph

Move `jikan-me/jikan-rest` from the archived Lumen 9 (Laravel 9, security-EOL) onto
**Laravel 13** with first-party tooling: `laravel/framework` + `mongodb/laravel-mongodb`
v5, Laravel Scout's **native Typesense engine** (replacing the third-party driver),
PHPUnit 12, `l5-swagger` docs, Octane (RoadRunner default) in place of the old
`lumen-roadrunner` package — while preserving the public v4 JSON contract for valid requests,
guarded by a committed parity harness and the integration suite. Developer tooling is first-party too:
`laravel/boost` (agent/skills CLI) with four Laravel marketplace skills
(`laravel-specialist`, `php-pro`, `laravel-security`, `laravel-patterns`) under `.ai/skills/`.

---

## 3. Evidence pack (for the PR body)

- **Parity harness** (`tools/parity/`): 59-endpoint status + response-shape comparison
  against a committed baseline; migration went **69 problems → 3** (2 intentional
  `/docs` redirect + 1 pre-existing `users.php` parser failure). Post-fixes re-verified:
  every change introduced **zero new shape/status diffs** — the remaining ~39 endpoint
  diffs at that point were the upstream parser blockers, cleared by the jikan v5
  adoption (`UPSTREAM_ISSUES.md`, §1).
- **Test suite**: 289 tests / 2696 assertions; only 1–2 failures, both the same
  time-dependent `SeasonControllerTest` season tests that failed on the Lumen baseline.
  (280 at migration green; +9 from the heartbeat + TTL-guard tests.)
- **Live smoke**: `/`, `/v4`, genres, producers, seasons, search, Swagger UI, `/up`,
  Octane (RoadRunner) — all 200.
- **Static analysis**: PHPStan **level 5** + 816-entry baseline → exit 0; Pint passes
  on the formatted subset (repo-wide formatting documented as a follow-up).
- **Config-cache safety**: `php artisan config:cache` builds and the app boots fully
  from cached config (formerly `env()` calls outside config silently nulled values).

---

## 4. RFC draft (for the `jikan-rest` issue)

```markdown
### RFC: Migrate jikan-rest from Lumen 9 to Laravel 13

**Status:** proposal — seeking buy-in before code review. _(Decision 2026-10-12: this RFC is **not** being filed — the PR series in §5 is the vehicle; these arguments + the open questions double as the cover letter and review prompts for the PRs.)_

**Motivation.** Lumen 9 is archived; Laravel 9 reached security end-of-life and the
Lumen/Mongo driver pairing can no longer be maintained on current PHP. The public v4
API contract is preserved for valid requests (parity harness and integration-suite evidence
attached; the harness has no invalid-input cases).

**Proposal.** Move to Laravel 13 with first-party packages: laravel/framework,
mongodb/laravel-mongodb v5, Scout's native Typesense engine (drops the third-party
driver + an RC-pinned client), Octane (RoadRunner default, FrankenPHP opt-in),
l5-swagger, PHPUnit 12. Read-only JSON API — no CSRF/sessions/auth migration surface.

**What does **not** change:** the v4 response shape, route paths, query parameter
contract, or the Mongo data layout.

**Target version: Laravel 13** (12 → 13 cost for this app is ~nil; 13 ships Scout's
native Typesense engine, enabling a smaller dependency set).

**Open questions for maintainers**
1. Accept Laravel 13 directly, or land a 12-based PR first then bump?
2. Octane default: RoadRunner (olds-style ports) OK as the default?
3. Keep the Goutte shim (upstream `jikan v4` still requires fabpot/goutte) or gate
   the jikan v5 upgrade behind this PR?

**Evidence.** Parity harness (status + shape, 60 endpoints), 280-test suite,
Octane-served endpoints. Links to the staged PR series below.
```

---

## 5. Delivery stack (supersedes the "14-PR series" and "single PR" framings)

> **Current model (2026-11-20):** `laravel-12` is the release line; each phase of
> issue #615 is its own branch and PR, merged in order on top of it. Only phase 3
> is implemented on the branch this doc was last updated from.

| Order | Branch | PR | Contents | State |
|---|---|---|---|---|
| — | `laravel-12` | #614 (draft) | Laravel 13 migration | open |
| 1 | `stack/615-03-response-efficiency` | #632 | `?fields=` on every v4 data response, bounded `?ids=`, weak ETag/`304`, `Cache-Control` | implemented — `docs/RESPONSE_FIELDS.md` |
| 2 | `stack/615-04-retrieval` | #633 | ranking, aliases, search benchmark | later in stack |
| 3 | `stack/615-05-identity-resilience` | #636 | data-quality checks, circuit breaker, stale fallback | later in stack |
| 4 | `stack/615-06-mcp-evaluation` | — | MCP evaluation | planned — plan only, no code |
| independent | `fix/616-search-index-drift` | #634 | test-harness `characters`/`people` text-index swap (issue #616) | branches from `laravel-12`, not stacked |

Merge #632 → #633 → #636 → phase 6. #634 can land on its own at any point.

The migration itself is still one body of work (draft PR #614). The table below is
the internal theme index of that work — historical, not a push order:

| # | Theme | Contents (local commits) | Notes |
|---|---|---|---|
| 1 | `test: add parity harness, PHPStan/Larastan + Pint + CI` | `tools/parity/*`, `phpstan.neon` + baseline, `pint.json`, `.github/workflows/ci.yml`, `UPSTREAM_ISSUES.md` | No behavior change; establishes the safety net first (T4 infra) |
| 1b | `feat: developer tooling — laravel/boost + skills + backend-quality-suite` | `composer.json` (+`laravel/boost` ^2 dev), `.ai/skills/*`, `.backend-suite/*` (profile/arch-rules/docs.manifest, ratified), `install.sh` + `docs/SETUP.md` | Optional/subjective; dev-only tooling — maintainers may want it split or dropped |
| 2 | `feat: Laravel 13 skeleton + bootstrap swap` | `bootstrap/app.php`, `public/index.php`, `artisan`, base classes, `routes/` split | App boots; v4 group still loads via `namespace` + string actions |
| 3 | `fix: Symfony 7/8 route constraints` | `{id}` + `->where()` conversions | The 69→3 parity fix |
| 4 | `feat: mongodb/laravel-mongodb v5 + spatie data v4` | driver swap, DTO/cast/`PreparesData` API adoption | |
| 5 | `feat: swagger via l5-swagger + drop abandoned packages` | l5-swagger, Swagger attributes, removed packages | |
| 6 | `feat: Octane with RoadRunner default` | octane + config (warm/flush), RR binary notes | FrankenPHP opt-in via `OCTANE_SERVER` |
| 7 | `feat: search — Scout native Typesense engine` | driver removal, `typesense-php` stable pin, config `client-settings` | Typesense-only decision |
| 8 | `chore: PHPUnit 12` | annotations→attributes, final-ctor migration, dead listener removal | |
| 9 | `security: config-cache safety, rate limiting, error redaction, cache-bypass removal` | env()→config, `throttle:v4`, Handler gating, cache-bypass header removed | |
| 10 | `perf+fixes: Octane flush completeness, lazy handlers, Scout pagination, cache TTL guard, test-isolation` | flush set, `DefaultMediator` factories, chunked-total fix, TTL map re-sync + guard test, prod-DB guard | The test-isolation guard is critical (T2 commits) |
| 11 | `feat: source heartbeat persisted in MongoDB` | `failovers` collection storage replacing `failovers.json`/lock files + prune/score logic | T1 commit |
| 12 | `chore: observability — log channels, Sentry defaults, /up health` | `config/logging.php`, `config/sentry.php`, health route | |
| 13 | `build: Dockerfile for PHP 8.4 + modern RoadRunner` | rewritten `Dockerfile`, `.dockerignore` | T3 commit |
| 14 | `docs: PR_CHANGELOG + audits + UPSTREAM_PR` | changelog + audit docs + this playbook | Reviewer orientation |

> This theme table is retained as the record of the base migration's contents; it is
> not a split plan and not a push order. The #615 phases in the stack above supersede
> any numbering here.

---

## 6. `jikan-me/jikan` contribution sequence — **resolved by v5 adoption**

UP-01…05 were **filed upstream on 2026-10-11** (`jikan-me/jikan` #598–#602, links in
`UPSTREAM_ISSUES.md`); UP-06 (goutte→v5) is already tracked upstream and will be
targeted *after* the Laravel migration lands. We are **not** actively fixing them
locally — when they (or we) pick them up, they ship as separate PRs to
`jikan-me/jikan` before/independent of the migration.

---

## 7. Checklist before pushing (status updated 2026-10-13)

- [ ] ~~Maintainer buy-in via the RFC (§4)~~ — **skipped by decision (2026-10-12): PR #614 is the vehicle**; field the §4 questions during review
- [ ] **Parity: full green on the remotely-runnable endpoints** — full index **stopped 2026-10-13** (decision): anime 10,399 docs + **20,053 retryable failed ids**; manga partial (2,191). Backfills are non-destructive and re-runnable anytime (`indexer:anime --failed`, `indexer:manga --failed`). Post-index (whenever run): parity re-verify + ratchet, Typesense re-import, endpoint smoke (`docs/POST_FULL_INDEX.md`). The suite (343 tests) is hermetic and does NOT depend on the live index.
- [ ] **Full suite green** — 331 / 2836 locally (perf/heartbeat + AI changes included); re-run on the exact commit going up
- [ ] **Composer finalized** — name/description = `jikan-me/jikan-rest`; `php: ^8.4.4`; `jikan-me/jikan: ^5.0` (v5.0.0); Sentry `dont-discover`; Goutte autoload dropped
- [x] **Dockerfile + compose validated locally** (Colima/arm64); **CI matrix + artifact-freshness job written** — first-green still needs a **real runner**
- [x] Observability reconciled (`/up`, Sentry REPORTING-gated, log channels) verified in suite
- [x] Secrets: no real tokens in source; `.env.dist` synced (Octane/CORS/Sentry/heartbeat keys)
- [x] Goutte-stance moot — jikan v5 adopted (UP-06 resolved)
- [ ] Decide `insights` removal + CORS stance with maintainers (**CORS restored as opt-in** to match master; insights still removed) — review conversation, not blocker
- [ ] Rebase `laravel-12` on `origin/master` (master static ~4 months) at go-time
- [ ] **Performance report** ready for the PR (`docs/PERFORMANCE.md` — matrix, wins: heartbeat debounce + gzip, robustness findings) — apply the report to the PR body when pushing
- [ ] Offer to co-maintain the migrated branch if upstream prefers to defer

---

## 8. Open decisions / follow-ups

- 12-first vs straight-to-13 (recommend 13; RFC Q1).
- Whether `insights`/CORS removals and other feature deletions are acceptable to
  upstream (they were deleted as unused; upstream may want them kept).
- Octane soak test and dev-DB index verification (see `PR_CHANGELOG` backlog).
- Keep this doc updated with whichever of these are resolved before push.