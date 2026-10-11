# Setup Guide — jikan-rest (Laravel 13)

This checkout runs **without Docker** (native PHP/Mongo) or **with Docker** (see `docs/DOCKER.md`).
It targets Laravel 13, PHP 8.4, MongoDB; Octane is the production runtime (RoadRunner default,
FrankenPHP opt-in), search is Laravel Scout (Typesense **or** the Mongo `$text` fallback).

> Machine-specific notes for the dev box used to build this fork are in
> `docs/LOCAL_SETUP.md`; the canonical/general instructions are **this file**.

---

## Quick start (native)

```bash
./install.sh --check                # verify prerequisites (safe)
./install.sh                        # minimal: Mongo + RoadRunner, local env
# then:
curl http://127.0.0.1:8000/up       # 200 OK
tools/dev/php vendor/bin/phpunit --no-coverage
```

### Options

| Flag | What it does |
|---|---|
| `--server roadrunner\|frankenphp\|serve` | Runtime server (default `roadrunner`). `serve` = plain `php artisan serve`; `frankenphp` downloads the opt-in static binary. |
| `--no-dev` | `composer install --no-dev` (production deps only). |
| `--with-redis` | Install/flag Redis (Homebrew) + `.env` keys. |
| `--with-typesense` | Download a native Typesense server (`/tmp/typesense`) + set `SCOUT_DRIVER=typesense`. |
| `--with-backend-suite` | `pip install` the `backend-quality-suite` CLI into `/tmp/bsq-venv` (drift audits — see `.backend-suite/`). |
| `--with-skills` | `composer require --dev laravel/boost` + install the four marketplace skills under `.ai/skills/`. |
| `--seed` | Run the unblocked indexers (genres + magazines). Anime/manga await jikan v5 (see `docs/UPSTREAM_ISSUES.md`). |
| `--env=local\|production` | Set `APP_ENV`. |
| `--check` | Prerequisites only. |

Examples:
```bash
./install.sh --with-backend-suite --with-skills                 # dev + quality + skills
./install.sh --with-typesense --server frankenphp --env=production --no-dev --seed
```

---

## Manual native setup (what the installer automates)

1. **PHP 8.4 + MongoDB driver**
   `brew install php@8.4 && pecl install mongodb`. (Herd-Lite's bundled PHP cannot upgrade
   `ext-mongodb` past 1.20 — use Homebrew `php@8.4`, run via the **`tools/dev/php`** wrapper:
   it unsets `PHP_INI_SCAN_DIR` which Homebrew requires.)
2. **MongoDB** — `brew install mongodb-community && brew services start mongodb-community`
   (default DB `jikan`, test DB `jikan_tests`).
3. **Deps** — `tools/dev/php /opt/homebrew/bin/composer install`
4. **Env** — `cp .env.dist .env && tools/dev/php artisan key:generate`
5. **Runtime**
   - RoadRunner (default): `tools/dev/php artisan octane:install --server=roadrunner` then
     `OCTANE_SERVER=roadrunner tools/dev/php artisan octane:start --server=roadrunner --port=8000`
   - FrankenPHP (opt-in): download the static binary, then
     `OCTANE_SERVER=frankenphp tools/dev/php artisan octane:start --server=frankenphp --port=8000`
   - Dev on the built-in server: `tools/dev/php artisan serve --host=127.0.0.1 --port=8000`
6. **Verify**
   ```bash
   curl http://127.0.0.1:8000/up                       # 200 OK
   tools/dev/php vendor/bin/phpunit --no-coverage       # 292 tests / 2707 asserts
   tools/dev/php artisan config:cache                   # config-cache safe
   ```

---

## Service / integration decisions

| Concern | Default | Options |
|---|---|---|
| Search | Mongo `$text` (no server needed) | Typesense via `SCOUT_DRIVER=typesense` + native binary or `docker compose` |
| Cache | `array` (local) | Redis in prod (`CACHE_DRIVER=redis`) |
| Runtime | RoadRunner | FrankenPHP (`OCTANE_SERVER=frankenphp`), `php artisan serve`, or Docker |
| Static analysis | PHPStan level 6 (baseline-pinned) + Pint | `composer phpstan` · `composer pint` |
| API docs | l5-swagger (spec at `v4/api-docs`) | UI endpoint disabled by design |
| Audits | `backend-quality-suite` (`.backend-suite/`) | CLI from `/tmp/bsq-venv` (or `pip install git+…@main`) |
| Agent skills | `laravel/boost` + `.ai/skills/*` | dev-only |

## Integration checks
```bash
/tmp/bsq-venv/bin/backend-suite audit rate-limit          # throttle:v4 covers every action
/tmp/bsq-venv/bin/backend-suite audit cache-ttl-coverage  # every route action has a TTL entry
/tmp/bsq-venv/bin/backend-suite audit error-shape         # envelope {status,type,message,error,report_url} intact
```

---

## Agent prompt (for an AI/agent to install this project)

> **Task:** install and verify the jikan-rest project at `<path>` natively (no Docker).
>
> 1. Run `./install.sh --check` and confirm the prerequisites; fix any missing
>    prerequisites (Homebrew `php@8.4` + `pecl install mongodb` + `mongodb-community`).
> 2. Run `./install.sh` (defaults: Mongo + RoadRunner, local env). If the operator wants
>    extra services, use `--with-typesense`, `--with-backend-suite`, `--with-skills`,
>    `--server frankenphp`.
> 3. Start the server (RoadRunner unless another `--server` was chosen) and verify:
>    `curl http://127.0.0.1:8000/up` → `200`, `curl http://127.0.0.1:8000/v4/genres/anime` → `200`.
> 4. Run `tools/dev/php vendor/bin/phpunit --no-coverage` → 292 tests / 2707 assertions green,
>    and `tools/dev/php artisan config:cache` boots.
> 5. If `--with-backend-suite` was requested, run the key audits
>    (`rate-limit`, `cache-ttl-coverage`, `error-shape`) and report results.
> 6. Report exactly what was installed, which server is running, and the health-check output.
> Do **not** push anything upstream; keep all work on the current branch.

---

## Related
- `docs/DOCKER.md` — full Docker Compose stack (app + Mongo + Redis + Typesense).
- `docs/LOCAL_SETUP.md` — machine-specific notes for the original dev box.
- `docs/TROUBLESHOOTING.md` · `docs/UPSTREAM_ISSUES.md` (what waits on jikan v5) ·
  `.backend-suite/` (quality audits) · `.ai/skills/` (agent skills).