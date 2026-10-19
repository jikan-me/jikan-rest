# 🐳 Docker: build, run, configure & seed `jikan-rest`

Local, PR-ready Docker stack for the Laravel 13 / Octane (RoadRunner / MongoDB)
app. The image does **not** run `php-fpm` — Octane workers are CLI processes
served by the RoadRunner binary on port 8080.

| File | Purpose |
|---|---|
| `Dockerfile` | App image: PHP 8.4-cli-alpine + RoadRunner 2025.1.15, `composer` deps cached, `route:cache` baked, HEALTHCHECK on `/up` |
| `docker-compose.yml` | Full local stack: `app` (built from the Dockerfile) + `mongo` + `redis`, imports Typesense |
| `docker-compose.typesense.yml` | Typesense service — single source of truth, imported via `include:` (also runnable standalone) |
| `DOCKER.md` | This file |

> **Already have docker images running locally?** If a `mongod` is already on
> `27017` on the host, drop the `mongo.ports` stanza in `docker-compose.yml`.

---

## 1. Prerequisites

- Docker (BuildKit) — Docker Desktop 24+ works.
- Docker Compose **v2.20+** (both compose files use the modern `include:` /
  `depends_on.condition` syntax; the legacy `version:` key is intentionally
  omitted).
- Compose interpolates `${VAR:-default}` from your shell environment or a
  `.env` file placed at the repo root.
- Apple Silicon (M1–M4) builds `linux/arm64` natively; the Dockerfile detects
  the architecture at build time and fetches the matching RoadRunner binary, so
  no build arg is needed (see §2).

---

## 2. Build

The Dockerfile downloads the RoadRunner server binary (2025.1.15) inside the
app stage, detecting the build platform from `uname -m` — so a plain
`docker compose build` fetches the correct `linux-amd64`/`linux-arm64` asset
with no build arg (see the header comment in `Dockerfile`).

### 2.1 Compose (recommended)

```bash
docker compose up -d --build
```

### 2.2 Build the image directly with `docker buildx`

```bash
# AMD64 / Intel / CI
docker buildx build --platform linux/amd64 -t jikan-rest:local .

# ARM64 (Apple Silicon, or cross-building for arm64 runners)
docker buildx build --platform linux/arm64 -t jikan-rest:local .

# Inspect the baked-in binary
docker run --rm --entrypoint /usr/local/bin/rr jikan-rest:local --version
```

> Cross-builds work automatically: under emulation the build container sees the
> target architecture, so the matching RoadRunner binary is fetched.

---

## 3. Run

```bash
docker compose up -d --build
docker compose ps                # app/mongo/redis/typesense all "healthy"
docker compose logs -f app       # Octane + supercronic output
docker compose down              # stop (keeps volumes)
docker compose down -v           # stop + wipe mongo/redis/typesense data
```

The `app` container's `ENTRYPOINT` (`docker-entrypoint.sh`) runs
`php artisan config:cache` at boot (runtime env vars are honoured), starts the
Laravel scheduler via supercronic, then `exec`s

```
php artisan octane:start --server=roadrunner --host=0.0.0.0 --port=8080
```

so the compose `app` service needs no `command:`. Extra Octane flags can be
appended via `command:` in `docker-compose.yml`, e.g. `command: ["--workers=8"]`.

Ports on the host: **8080** (app/Octane), **27017** (MongoDB,
`127.0.0.1`-bound), **8108** (Typesense, `127.0.0.1`-bound). Redis is not
exposed to the host by default (uncomment if you want `redis-cli`).

---

## 4. Configuration / environment variables

Everything is mirrored from `.env.dist`; the entrypoint copies `.env.dist` into
`.env` when absent, and the values below override those defaults. Set any of
them as `VAR=value docker compose ...` or in a root `.env` file.

| Variable | Default | Notes |
|---|---|---|
| `APP_KEY` | *(empty)* | **Set this.** Generate on the host: `php artisan key:generate`, then pass the `base64:...` value, e.g. `APP_KEY=base64:... docker compose up -d app`. Empty is fine for purely read-only GET dev, but encryption-backed features (sessions/cookies) need it. |
| `APP_ENV` | `production` | `APP_DEBUG=false` keeps error responses redacted. |
| `APP_URL` | `http://localhost:8080` | |
| `DB_HOST` / `DB_PORT` / `DB_DATABASE` | `mongo` / `27017` / `jikan` | Points at the compose `mongo` service. |
| `DB_USERNAME` / `DB_PASSWORD` | *(empty)* | No auth on the local Mongo (config/database.php builds `mongodb://mongo:27017/admin`). To enable auth, set `MONGO_INITDB_ROOT_USERNAME/PASSWORD` on `mongo` **and** `DB_USERNAME`/`DB_PASSWORD` on `app`. |
| `REDIS_HOST` / `REDIS_PORT` | `redis` / `6379` | Only used when caching via Redis. Leave `REDIS_PASSWORD` unset for no auth. |
| `SCOUT_DRIVER` | `null` | **Search off by default.** Flip to `typesense` (with `TYPESENSE_API_KEY`) to enable scout — see §5. |
| `TYPESENSE_HOST` / `TYPESENSE_PORT` | `typesense` / `8108` | |
| `TYPESENSE_API_KEY` | `abcd` | Shared by the `typesense` service **and** the app — override once: `TYPESENSE_API_KEY=my-key docker compose up -d` |
| `CACHE_DRIVER` | `file` | `storage/framework/cache/data`, shared by all Octane workers in the container. Do not use `array` under Octane: it is flushed before every request, so nothing is cached between requests. To share across containers: `CACHE_DRIVER=redis docker compose up -d app` |
| `OCTANE_SERVER` | `roadrunner` | Matches `config/octane.php`; keep as-is. |
| `JIKAN_ENABLE_PERIODICAL_FULL_INDEXER` | — | Set `true` to schedule a weekly full anime/manga re-index on Mondays 01:00 (supercronic). |

> ⚠️ The Dockerfile bakes `route:cache` at build time with `DISABLE_USER_LISTS`
> defaulting to `false`. If you change `DISABLE_USER_LISTS`, rebuild the image
> so the cached routes match.

---

## 5. Enabling Typesense search

Search is disabled by default (`SCOUT_DRIVER=null`). To turn it on:

```bash
# 1. (re)start with the typesense driver + the matching API key
TYPESENSE_API_KEY=abcd SCOUT_DRIVER=typesense docker compose up -d app

# 2. verify Typesense is healthy
curl -fsS http://localhost:8108/health           # -> {"ok":true}

# 3. pre-create collections — REQUIRED, the native Scout TypesenseEngine cannot
#    auto-create one from an empty schema. Follow docs/TYPESENSE_SPIKE.md
#    "Pre-create collections" (curl calls) or a typesense:ensure-collections
#    command if available, then import:
docker compose exec app php artisan scout:import "App\Anime"
docker compose exec app php artisan scout:import "App\Producers"
docker compose exec app php artisan scout:import "App\Magazine"
```

---

## 6. Seeding data (`artisan indexer:*`)

A fresh stack has an **empty database**. Fill it with the MyAnimeList scraper
from inside the container:

```bash
docker compose exec app php artisan indexer:anime --fail
docker compose exec app php artisan indexer:anime --resume   # after an interrupt
docker compose exec app php artisan indexer:manga --fail
docker compose exec app php artisan indexer:manga --resume
```

- This scrapes MAL and can take **days** for a full run. `--resume` continues an
  interrupted run from where it left off.
- Enable a weekly automated full re-index with
  `JIKAN_ENABLE_PERIODICAL_FULL_INDEXER=true`.
- Discover available indexer commands: `docker compose exec app php artisan list | grep indexer`.

---

## 7. Health checks

| Check | Command | Expected |
|---|---|---|
| App liveness | `curl -fsS http://localhost:8080/up` | `200 OK` ("OK") — no DB dependency |
| App metrics / heartbeat | `curl -fsS http://localhost:8080/` | `200` JSON incl. `myanimelist_heartbeat` |
| Public endpoint (after seeding) | `curl -fsS http://localhost:8080/v4/anime/1` | `200` anime JSON |
| Search endpoint (after §5) | `curl "http://localhost:8080/v4/producers?q=studio"` | `200` + results |
| Compose health | `docker compose ps` | all services `healthy` |
| Logs | `docker compose logs -f app` | Octane worker + access logs |

Both the Dockerfile `HEALTHCHECK` and the compose `app` healthcheck target
`GET /up`. Mongo/Typesense/Redis each have their own healthchecks wired into
`depends_on.condition: service_healthy`, so the app won't start before its
backends are ready.

---

## 8. Please note: no Docker on the dev box

Docker was **not available** while preparing these files, so the following were
validated without a daemon:

- YAML lint of both compose files (`symfony/yaml` via `tools/dev/php`), including
  interpolation syntax.
- Every env var used in compose cross-checked against `.env.dist` and
  `config/*` (`octane.php`, `scout.php`, `database.php`, `cache.php`).
- Ports/services cross-checked against `config/octane.php:8080`,
  `config/scout.php:8108` and `config/database.php:27017`.
- `tools/dev/php artisan config:cache` boots the app config end-to-end.

**Validation checklist for a Docker host (CI or a runner) before pushing:**

> Status: **the full build + stack was validated locally (2026-10-12) via Colima
> on macOS/arm64** — image builds, `rr --version` prints 2025.1.15, and all four
> services come up healthy. Checklist items below up to "Seed minimally" are
> confirmed; the last one needs data (upstream-blocked).

- [x] `docker compose up -d --build` — app image builds, `rr --version` prints
      2025.1.15 (arch auto-detected).
- [x] `docker compose ps` shows `app`, `mongo`, `redis`, `typesense` healthy.
- [x] `curl -fsS http://localhost:8080/up` returns `200 OK`.
- [x] `curl -fsS http://localhost:8080/` returns `200` JSON with the heartbeat block.
- [x] `docker compose exec app php artisan about` boots Octane container fine.
- [ ] Seed minimally + smoke `GET /v4/anime/:id` (200) — blocked: empty DB
      returns a `ParserException` 500 until anime data exists (upstream parser
      blockers, see `UPSTREAM_ISSUES.md`).