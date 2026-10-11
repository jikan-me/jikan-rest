# jikan-rest — Design (product scope)

> Note: mirrors the `design` doc role of the backend-quality-suite (not yet
> shipped in the released suite as of 2026-10-12; this file maps to it when it
> lands). Companion: `docs/ARCHITECTURE.md` (how), `docs/PRODUCT_…` see
> `docs/PROVIDERS.md` (deployment shape).

## 1. What Jikan covers

A public, read-only REST API that exposes MyAnimeList data as JSON — the
official MyAnimeList API alternative for developers. Scope on the v4 surface
(106 GET endpoints):

- **Content lookups** — anime, manga, characters, people (main + sub-resources)
- **Catalogs / lists** — produces, producers, magazines, genres, top lists,
  seasons, schedules, random
- **Community (limited)** — user profile lookups; `users/recentlyonline`
  (live-verified working on jikan v5, 2026-10-12)
- **Meta** — API root heartbeat (`/`), `/v4` heartbeat, generated docs
  (l5-swagger), `/up` liveness

Data originates from scraped MyAnimeList pages (jikan parser), stored in
MongoDB, served by Laravel 13.

## 2. What Jikan deliberately does **not** do

- **No write path** — no post/put/delete; no submission or "for later"
  mutations.
- **No auth, sessions, CSRF** — fully public read-only; no per-user state.
- **No web UI** — JSON API only; no Blade/templating.
- **No per-user or social features** — lists are public profile data only.
- **No real-time/push** — HTTP polling; caching via per-endpoint TTL
  (`per_endpoint_cache_ttl` in `config/jikan`).

## 3. Conventions

- **Errors** — uniform envelope `{status, type, message}`; 404/500 standard
  shapes; 429 from `throttle:v4` rate limiting.
- **Pagination** — `page`/`per_page` (config-capped), envelope
  `pagination.items.{count,total,per_page}`; `jikanPaginate()` handles Scout >
  250-hit chunking.
- **Search** — optional Typesense; `q=` applies per-model `query_by` with
  weights/sort; ranking verified end-to-end (see `docs/TYPESENSE_SPIKE.md`).
- **Documentation** — generated OpenAPI (l5-swagger) + parity harness as the
  source of truth for the contract.

## 4. User flows (canonical)

1. **Look up an anime/manga/character/person** `GET /v4/anime/{id}` →
   parsed detail JSON (data-gated: upstream parser blockers #598–602).
2. **Browse catalogs** `GET /v4/top/anime`, `/v4/seasons/now`,
   `/v4/schedules?filter=` → paginated lists (verified 200 in smoke).
3. **Search** `GET /v4/anime?q=…` (Typesense) or producer/magazine search →
   ranked results with pagination.
4. **Health/ops** `GET /up`, root heartbeat → liveness for load balancers.

## 5. Non-features explicitly accepted

- Elasticsearch search engine removed — Typesense-only decision (2026-10-10).
- ~~Goutte→jikan v5 upgrade deferred (local shim)~~ — **resolved**: jikan v5.0.0-beta
  integrated 2026-10-12 (Goutte dropped; shim + composer `replace` removed).
- Multi-language/i18n beyond existing title types: out of scope.

---

_Last updated: 2026-10-12_