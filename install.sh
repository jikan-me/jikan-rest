#!/usr/bin/env bash
#
# jikan-rest installer — native (non-Docker) setup.
#
# Options-driven installer with sensible defaults. Everything stays on this
# checkout branch and no services are installed globally unless you ask for them.
#
#   ./install.sh [options]
#
# Options
#   --server roadrunner|frankenphp|serve   runtime server (default: roadrunner)
#   --no-dev                               production deps only (--no-dev)
#   --with-redis                           install/enable Redis (brew/mongosh check)
#   --with-typesense                       download + run a native Typesense server
#   --with-backend-suite                   pip-install the backend-quality-suite CLI
#   --with-skills                          composer require laravel/boost + install skills
#   --seed                                 run the indexable indexers (genres/common)
#   --env=local|production                 cardinal env (default: local)
#   --check                                only verify prerequisites, then exit
#   --help                                 show this help
#
# Examples
#   ./install.sh                                  # minimal (Mongo + RoadRunner, local)
#   ./install.sh --with-typesense --server frankenphp --with-backend-suite --with-skills
#   ./install.sh --env=production --no-dev --with-redis --seed
#
# Non-interactive: every flag defaults safely; nothing prompts mid-run.

set -euo pipefail

SERVER="${SERVER:-roadrunner}"
DEV_DEPS=1
REDIS=0; TYPESENSE=0; BACKEND_SUITE=0; SKILLS=0; SEED=0
APP_ENV_MODE="local"
CHECK_ONLY=0

while [[ $# -gt 0 ]]; do
  case "$1" in
    --server)        SERVER="$2"; shift 2 ;;
    --server=*)      SERVER="${1#*=}"; shift ;;
    --no-dev)        DEV_DEPS=0; shift ;;
    --with-redis)    REDIS=1; shift ;;
    --with-typesense) TYPESENSE=1; shift ;;
    --with-backend-suite) BACKEND_SUITE=1; shift ;;
    --with-skills)   SKILLS=1; shift ;;
    --seed)          SEED=1; shift ;;
    --env=*)         APP_ENV_MODE="${1#*=}"; shift ;;
    --check)         CHECK_ONLY=1; shift ;;
    --help)          sed -n '2,32p' "$0"; exit 0 ;;
    *) echo "Unknown option: $1 (see ./install.sh --help)"; exit 2 ;;
  esac
done

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

PHP="${PHP_BIN:-$ROOT/tools/dev/php}"
COMPOSER="${COMPOSER_BIN:-$(command -v composer || echo /opt/homebrew/bin/composer)}"

say()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m!  %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }
have() { command -v "$1" >/dev/null 2>&1; }

# ---------------------------------------------------------------- prerequisites
check_prereqs() {
  say "Prerequisites"
  [[ -x "$PHP" ]] || die "PHP wrapper not found at $PHP (expected tools/dev/php — Homebrew php@8.4 + ext-mongodb)."
  "$PHP" -r 'exit(extension_loaded("mongodb") ? 0 : 1);' 2>/dev/null \
    && echo "  ✓ ext-mongodb" \
    || warn "  ✗ ext-mongodb missing (pecl install mongodb)."
  "$PHP" -r 'exit(extension_loaded("pcntl") ? 0 : 1);' 2>/dev/null \
    && echo "  ✓ ext-pcntl" \
    || warn "  ✗ ext-pcntl missing (Octane needs it)."
  have mongosh && (mongosh --quiet --eval 'db.runCommand({ping:1}).ok' >/dev/null 2>&1 \
    && echo "  ✓ MongoDB reachable" || warn "  ✗ MongoDB not reachable (brew services start mongodb-community)") \
    || warn "  ✗ mongosh not found (brew install mongodb-community)."
  echo "  ✓ PHP: $("$PHP" -v | head -1)"
  echo "  ✓ Composer: $COMPOSER"
}

if [[ "$CHECK_ONLY" == "1" ]]; then check_prereqs; exit 0; fi
check_prereqs

# ------------------------------------------------------------------ env + keys
if [[ ! -f .env ]]; then
  say "Creating .env from .env.dist"
  cp .env.dist .env
fi
if ! grep -q '^APP_KEY=' .env || [[ -z "$(grep '^APP_KEY=' .env | cut -d= -f2-)" ]]; then
  say "Generating APP_KEY"
  "$PHP" artisan key:generate --force
fi
sed -i '' "s/^APP_ENV=.*/APP_ENV=$APP_ENV_MODE/" .env 2>/dev/null \
  || sed -i "s/^APP_ENV=.*/APP_ENV=$APP_ENV_MODE/" .env

# ------------------------------------------------------------------- composer
say "Composer install"
if [[ "$DEV_DEPS" == "1" ]]; then
  "$PHP" "$COMPOSER" install --no-interaction
else
  "$PHP" "$COMPOSER" install --no-interaction --no-dev --optimize-autoloader
fi

# --------------------------------------------------------------------- services
if [[ "$REDIS" == "1" ]]; then
  say "Redis"
  command -v redis-server >/dev/null 2>&1 && echo "  ✓ redis-server present" \
    || (brew install redis >/dev/null 2>&1 && echo "  ✓ redis installed via Homebrew")
  [[ -f .env ]] && grep -q '^REDIS_HOST' .env || printf 'REDIS_HOST=127.0.0.1\nREDIS_PORT=6379\n' >> .env
fi

if [[ "$TYPESENSE" == "1" ]]; then
  say "Typesense (native binary, ports 8108)"
  TYPESENSE_DIR="${TYPESENSE_DIR:-/tmp/typesense}"
  if [[ ! -x "$TYPESENSE_DIR/typesense-server" ]]; then
    mkdir -p "$TYPESENSE_DIR"
    ARCH="$(uname -m)"; OS="$(uname | tr '[:upper:]' '[:lower:]')"
    URL="https://dl.typesense.org/releases/26.0/typesense-server-26.0-${OS}-${ARCH}.tar.gz"
    echo "  downloading $URL"
    curl -fsSL -o /tmp/typesense.tar.gz "$URL" && tar -xzf /tmp/typesense.tar.gz -C "$TYPESENSE_DIR"
  fi
  grep -q '^TYPESENSE_API_KEY' .env || printf 'TYPESENSE_API_KEY=abcd\nSCOUT_DRIVER=typesense\n' >> .env
  echo "  run it with: $TYPESENSE_DIR/typesense-server --data-dir=/tmp/typesense-data --api-key=abcd --port=8108"
fi

if [[ "$BACKEND_SUITE" == "1" ]]; then
  say "backend-quality-suite CLI (Python venv)"
  python3 -m venv /tmp/bsq-venv 2>/dev/null || die "python3 -m venv failed"
  /tmp/bsq-venv/bin/pip install -q "git+https://github.com/irfan-dahir/backend-quality-suite.git@main"
  export PATH="$ROOT/tools/dev:/tmp/bsq-venv/bin:$PATH"
  /tmp/bsq-venv/bin/backend-suite doctor || true
fi

if [[ "$SKILLS" == "1" ]]; then
  say "Laravel skills (laravel/boost)"
  "$PHP" "$COMPOSER" require --dev laravel/boost --no-interaction
  "$PHP" artisan package:discover >/dev/null 2>&1 || true
  "$PHP" artisan boost:add-skill jeffallan/claude-skills --skill laravel-specialist || true
  "$PHP" artisan boost:add-skill jeffallan/claude-skills --skill php-pro || true
  "$PHP" artisan boost:add-skill affaan-m/ecc --skill laravel-security || true
  "$PHP" artisan boost:add-skill affaan-m/everything-claude-code --skill laravel-patterns || true
fi

# ------------------------------------------------------------------------- data
if [[ "$SEED" == "1" ]]; then
  say "Seeding indexable data (genres + magazines)"
  "$PHP" artisan indexer:genres >/dev/null 2>&1 || true
  "$PHP" artisan indexer:common >/dev/null 2>&1 || true
  echo "  (anime/manga wait on jikan v5 — see docs/UPSTREAM_ISSUES.md)"
fi

# ----------------------------------------------------------------------- server
case "$SERVER" in
  serve)
    say "Ready: php artisan serve"
    printf '\nRun:  %s artisan serve --host=127.0.0.1 --port=8000\n' "$PHP"
    ;;
  roadrunner)
    say "RoadRunner"
    if [[ ! -x ./rr ]]; then
      "$PHP" artisan octane:install --server=roadrunner >/dev/null 2>&1 || true
    fi
    [[ -x ./rr ]] && echo "  ✓ rr binary ($(./rr --version 2>/dev/null | head -1))" \
      || warn "  rr binary not found — ./install.sh --server roadrunner downloads it on first octane:start"
    printf '\nRun:  OCTANE_SERVER=roadrunner %s artisan octane:start --server=roadrunner --port=8000\n' "$PHP"
    ;;
  frankenphp)
    say "FrankenPHP (opt-in — no binary is shipped in this repo)"
    FPHP_DIR="${FPHP_DIR:-/tmp/frankenphp}"
    mkdir -p "$FPHP_DIR"
    ARCH="$(uname -m | sed 's/x86_64/amd64/;s/aarch64/arm64/')"; OS="$(uname | tr '[:upper:]' '[:lower:]')"
    URL="https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-${OS}-${ARCH}"
    if [[ ! -x "$FPHP_DIR/frankenphp" ]]; then
      echo "  downloading $URL"
      curl -fsSL -o "$FPHP_DIR/frankenphp" "$URL" && chmod +x "$FPHP_DIR/frankenphp"
    fi
    printf '\nRun:  OCTANE_SERVER=frankenphp %s artisan octane:start --server=frankenphp --port=8000\n' "$PHP"
    ;;
  *) die "Unknown --server=$SERVER (roadrunner|frankenphp|serve)" ;;
esac

# --------------------------------------------------------------------- summary
say "Install complete"
"$PHP" artisan --version
echo
echo "  API:      http://127.0.0.1:8000  (or :8080 under Octane)"
echo "  Health:   curl http://127.0.0.1:8000/up"
echo "  Docs:     docs/SETUP.md · docs/DOCKER.md (Docker path) · .backend-suite/ (quality audits)"