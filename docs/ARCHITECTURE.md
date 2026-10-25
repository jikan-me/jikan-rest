# jikan-rest — Architecture

> Note: this document is authored to match the `architecture` doc role of the
> backend-quality-suite. That role is not yet shipped in the released suite
> (v0.1.0/`main` as of 2026-10-12); when it lands upstream, this file maps in as
> `docs/ARCHITECTURE.md`. The machine-readable authority for the layer contract
> is `.backend-suite/arch-rules.json`.

## 1. System shape

Read-only public v4 JSON API over MyAnimeList data. Laravel **13** (PHP 8.4),
MongoDB via `mongodb/laravel-mongodb` **v5**, served by **Octane** (Swoole
default) with optional Scout **Typesense** search. Public contract: 106 GET
endpoints, no auth, no sessions, no Blade.

```
HTTP → Octane worker (Swoole)
     → routes/ (v4 namespace) → Controllers (app/Http/Controllers/V4DB/**)
     → DTO commands → DefaultMediator (lazy factory map, ~101 entries)
     → request handlers (Query*Handler / Command handlers)
     → services (app/Services) + repositories (app/Repositories/…)
     → models (app/{Anime,Manga,Character,…}) → Mongo collections
     → ApiResponse envelope / standard error envelope {status,type,message}
```

## 2. Layers (enforced)

From `.backend-suite/arch-rules.json` (`layerBoundaries.rules`), enforced by
`bin/audit_layer_boundaries.py`:

- **HTTP layer** (`app/Http/**`) must **not** import the **Service layer**
  (`app/Services/**`, `App\Services\`).
- **Service layer** must **not** import the **HTTP layer** (`app/Http/**`,
  `App\Http\`).

Controllers are scanned from `app/Http/Controllers/V4DB/**` (`controllerDirs` —
route coverage and api-consistency audits use this). Route actions are class-based
(`[Controller::class, 'method']`) and named `v4.<resource>.<action>` (`route-coverage`
resolves every action).

## 3. Request path in detail

1. **Routing** — `routes/` registers the v4 group; actions are class-based and
   named, and `{id}` constraints live in the group attributes (`'where' => [...]`).
2. **Mediation** — `DefaultMediator` resolves a handler from a `DTO class =>
   factory` map (built per request from the request's own container, so repositories
   never outlive a request under Octane), and a request constructs only the handler
   it dispatches (no ~65-handler build per request).
3. **Handlers/services** — handlers translate commands to repository/service
   calls and produce the API response surface; repositories read/target Mongo.
4. **Models** — `JikanApiModel` base; searchable models extend
   `JikanApiSearchableModel` (Scout contract: `typesenseCollectionSchema()`
   / `typesenseSearchParameters()`). `getScoutKey()` = `mal_id`.

## 4. Persistence

- Mongo collections mirror the v4 model surface; indexes tracked in
  `docs/DB_INDEXES.md` (134 squash-migration indexes verified present; compound
  `schedule`/`season`/`top_airing` indexes added 2026-10-11).
- Config (`JikanConfig`) merges DB-backed per-endpoint cache TTL + search
  options over `config/jikan` defaults.

## 5. Runtime & ops

- **Image** (`Dockerfile`): `php:8.4-cli-alpine`, `ext-mongodb`, `pcntl/
  sockets/opcache`, `ext-swoole`/`ext-brotli` (PECL), `route:cache`
  baked at build, `config:cache` in the entrypoint so runtime env applies.
- **Entrypoint** (`docker-entrypoint.sh` → `docker-entrypoint.php`): prepares
  `.env` from `.env.dist`, overlays safe defaults + Docker secrets, caches
  config, starts supercronic + `octane:start`.
- **Health/liveness**: `GET /up` → 200 (explicit route; the built-in
  `health:` option is not used with a custom `using` closure).

## 6. Search

- Default off (`SCOUT_DRIVER=null`); flip `SCOUT_DRIVER=typesense` (+ key).
- Native Scout `TypesenseEngine` via the model contract — **no** `model-settings`
  config; collections auto-create on import. Chunked client-side pagination
  via `app/Mixins/ScoutBuilderMixin` (>250-hit path verified).

## 7. Quality guarantees

`tools/parity/` (status+shape contract harness, committed baseline), PHPStan
level 6 (baseline-pinned), Pint (scoped), PHPUnit 12 (292 tests / 2707
assertions), `.backend-suite` audits (endpoint-parity / route-coverage /
layer-boundaries clean; documented residuals only).

---

_Last updated: 2026-10-12_