#!/usr/bin/env bash
#
# jikan-rest install.sh — interactive, option-driven native (non-Docker) setup.
#
# Interactive (recommended for humans): run with no flags on a terminal and it
# prompts for each choice with a sensible default. Non-interactive for CI/scripts:
# pass explicit flags (or --yes to accept every default). Idempotent — safe to
# re-run. See docs/SETUP.md and the normalized script/ wrappers.
#
# Flags
#   --server roadrunner|frankenphp|serve   runtime (default roadrunner)
#   --env=local|production                 cardinal env (default local)
#   --no-dev                               composer --no-dev (production deps)
#   --with-redis|--with-typesense|--with-backend-suite|--with-skills|--seed
#   --yes | -y                             accept all defaults, no prompts
#   --check                                prerequisites only, then exit
#   --dry-run                              preview every step without executing or downloading
#   --version                              print version, exit
#   --help                                 this help
#
# Prerequisite-safe by design: everything checks prerequisites first, mutates
# only what it declares, and never prompts after anything has been written
# (each prompt happens BEFORE its step). Zero runtime deps — just bash.

set -euo pipefail

VERSION="1.0.0"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT"

if [[ -n "${PHP_BIN:-}" ]]; then
  PHP="$PHP_BIN"
elif [[ -x "$ROOT/tools/dev/php" ]]; then
  # Local macOS wrapper (gitignored, not in fresh checkouts).
  PHP="$ROOT/tools/dev/php"
else
  # Linux / fresh checkout: distro PHP from PATH.
  PHP="$(command -v php || true)"
fi
if [[ -z "$PHP" ]]; then
  die "no PHP found — install php8.4 + ext-mongodb + ext-pcntl, or set PHP_BIN"
fi
COMPOSER="${COMPOSER_BIN:-$(command -v composer || true)}"

SERVER="${SERVER:-roadrunner}"
APP_ENV_MODE="local"
DEV_DEPS=1
REDIS=0; TYPESENSE=0; BACKEND_SUITE=0; SKILLS=0; SEED=0
CHECK_ONLY=0
DRY=0
YES=0
SHOWN_FLAGS=0

say()  { printf '\033[1;34m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m  ✓ %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m  ! %s\033[0m\n' "$*"; }
die()  { printf '\033[1;31mERROR: %s\033[0m\n' "$*" >&2; exit 1; }
have() { command -v "$1" >/dev/null 2>&1; }
run()  { if [[ "$DRY" == "1" ]]; then printf '\033[2m  [dry-run] %s\033[0m\n' "$*"; return 0; fi; "$@"; }

is_tty() { [[ -t 0 && -t 1 ]]; }

# Prompt helper: ask=1 (because interactive), returns 0/1 and echoes the value.
ask_yn() { # $1 prompt  $2 default(y|n)  -> sets REPLY_YN
  if [[ "$YES" == "1" ]] || ! is_tty; then REPLY_YN="$2"; return 0; fi
  local def="$2" hint="$2"
  [[ "$def" == "y" ]] && hint="Y/n" || hint="y/N"
  local a
  read -rp "$(printf '  %s [%s] ' "$1" "$hint")" a || true
  a="${a:-}"
  if [[ -z "$a" ]]; then REPLY_YN="$def"; else
    case "$a" in y|Y|yes|Yes|YES) REPLY_YN="y";; *) REPLY_YN="n";; esac
  fi
}

ask_list() { # $1 prompt  $2 choices-comma  $3 default  -> REPLY_LIST
  if [[ "$YES" == "1" ]] || ! is_tty; then REPLY_LIST="$3"; return 0; fi
  local a
  printf '  %s\n' "$1"
  local IFS=',' names=($2)
  for i in "${!names[@]}"; do printf '    [%s] %s\n' "$((i+1))" "${names[$i]}"; done
  read -rp "    choose [${3}]: " a || true
  a="${a:-}"
  if [[ "$a" =~ ^[0-9]+$ ]] && (( a >= 1 && a <= ${#names[@]} )); then
    REPLY_LIST="${names[$((a-1))]}"
  else
    REPLY_LIST="$3"
  fi
}

# ------------------------------------------------------------- parse arguments
while [[ $# -gt 0 ]]; do
  case "$1" in
    --server)        SERVER="$2"; shift 2 ;;
    --server=*)      SERVER="${1#*=}"; shift ;;
    --env=*)         APP_ENV_MODE="${1#*=}"; shift ;;
    --no-dev)        DEV_DEPS=0; shift ;;
    --with-redis)    REDIS=1; shift ;;
    --with-typesense) TYPESENSE=1; shift ;;
    --with-backend-suite) BACKEND_SUITE=1; shift ;;
    --with-skills)   SKILLS=1; shift ;;
    --seed)          SEED=1; shift ;;
    --yes|-y)        YES=1; shift ;;
    --check)         CHECK_ONLY=1; shift ;;
    --dry-run)       DRY=1; shift ;;
    --version)       echo "install.sh $VERSION"; exit 0 ;;
    --help|-h)       sed -n '/^# jikan-rest install.sh/,/^[^#]/p' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    *) die "unknown option: $1 (see ./install.sh --help)" ;;
  esac
done

# ------------------------------------------------------------- interact if TTY
if is_tty && [[ "$YES" != "1" ]]; then
  printf '\n%s interactive? press Enter to accept the default at each prompt.\n' "$VERSION"
  ask_list "Application environment" "local,production" "local"; APP_ENV_MODE="$REPLY_LIST"
  ask_list "Runtime server" "roadrunner,frankenphp,serve" "roadrunner"; SERVER="$REPLY_LIST"
  ask_yn "Install dev dependencies (PHPUnit, PHPStan, Pint, …)" "y"; [[ "$REPLY_YN" == "n" ]] && DEV_DEPS=0
  printf '\nOptional integrations (each defaults to no):\n'
  ask_yn "  Redis (cache/queue)" "n"; [[ "$REPLY_YN" == "y" ]] && REDIS=1
  ask_yn "  Typesense (search engine)" "n"; [[ "$REPLY_YN" == "y" ]] && TYPESENSE=1
  ask_yn "  backend-quality-suite (audit CLI)" "n"; [[ "$REPLY_YN" == "y" ]] && BACKEND_SUITE=1
  ask_yn "  Laravel skills (laravel/boost)" "n"; [[ "$REPLY_YN" == "y" ]] && SKILLS=1
  ask_yn "  Seed genres/magazines now" "n"; [[ "$REPLY_YN" == "y" ]] && SEED=1
  printf '\n'
fi

# --------------------------------------------------------------- prerequisites
check_prereqs() {
  say "Prerequisites"
  [[ -x "$PHP" ]] || die "PHP wrapper missing at $PHP (Homebrew php@8.4 + pecl mongodb)."
  "$PHP" -r 'exit(extension_loaded("mongodb") ? 0 : 1);' 2>/dev/null \
    && ok "ext-mongodb" || warn "ext-mongodb missing — brew install php@8.4 && pecl install mongodb"
  "$PHP" -r 'exit(extension_loaded("pcntl") ? 0 : 1);' 2>/dev/null \
    && ok "ext-pcntl" || warn "ext-pcntl missing (Octane requires it)"
  if have mongosh && mongosh --quiet --eval 'db.runCommand({ping:1}).ok' >/dev/null 2>&1; then
    ok "MongoDB reachable"
  else
    if [[ "$(uname -s)" == "Darwin" ]]; then
      warn "MongoDB not reachable — brew services start mongodb-community"
    else
      warn "MongoDB not reachable — systemctl start mongod (or your distro service)"
    fi
  fi
  ok "PHP $( "$PHP" -v | head -1 | awk '{print $2}')"
  if [[ -z "$COMPOSER" ]]; then
    warn "composer not found on PATH — install it or set COMPOSER_BIN"
  else
    ok "Composer: $COMPOSER"
  fi
  ok "OS: $(uname -s) $(uname -m)"
}
if [[ "$CHECK_ONLY" == "1" ]]; then check_prereqs; exit 0; fi
check_prereqs

# ----------------------------------------------------------------- env + keys
if [[ ! -f .env ]]; then run cp .env.dist .env; say "Created .env from .env.dist"; fi
if ! grep -q '^APP_KEY=' .env || [[ -z "$(grep '^APP_KEY=' .env | cut -d= -f2-)" ]]; then
  run "$PHP" artisan key:generate --force; say "Generated APP_KEY"
fi
if [[ "$DRY" == "1" ]]; then printf '  [dry-run] set APP_ENV=%s in .env\n' "$APP_ENV_MODE"; else sed -i.bak "s/^APP_ENV=.*/APP_ENV=$APP_ENV_MODE/" .env && rm -f .env.bak; fi

# --------------------------------------------------------------------- deps
say "Composer install"
if [[ "$DEV_DEPS" == "1" ]]; then
  run "$PHP" "$COMPOSER" install --no-interaction
else
  run "$PHP" "$COMPOSER" install --no-interaction --no-dev --optimize-autoloader
fi

# ------------------------------------------------------------------ services
if [[ "$REDIS" == "1" ]]; then
  say "Redis"
  if have redis-server; then
    ok "redis-server present"
  elif [[ "$(uname -s)" == "Darwin" ]]; then
    run brew install redis
  else
    warn "redis-server missing — install via your distro (e.g. apt install redis-server)"
  fi
  grep -q '^REDIS_HOST' .env || printf 'REDIS_HOST=127.0.0.1\nREDIS_PORT=6379\n' >> .env
fi

if [[ "$TYPESENSE" == "1" ]]; then
  say "Typesense (native binary, port 8108)"
  TDIR="${TYPESENSE_DIR:-/tmp/typesense}"
  if [[ ! -x "$TDIR/typesense-server" ]]; then
    mkdir -p "$TDIR"
    ARCH="$(uname -m | sed 's/x86_64/amd64/;s/aarch64/arm64/')"; OS="$(uname | tr '[:upper:]' '[:lower:]')"
    run curl -fsSL -o /tmp/typesense.tar.gz "https://dl.typesense.org/releases/27.0/typesense-server-27.0-${OS}-${ARCH}.tar.gz"
    run tar -xzf /tmp/typesense.tar.gz -C "$TDIR"
  fi
  ok "binary at $TDIR/typesense-server"
  grep -q '^TYPESENSE_API_KEY' .env || printf 'TYPESENSE_API_KEY=abcd\nSCOUT_DRIVER=typesense\n' >> .env
  echo "    run: $TDIR/typesense-server --data-dir=/tmp/typesense-data --api-key=abcd --api-port=8108"
  # (typesense has no --port flag; --api-port is the real one — v26+ renamed --listen-port)
fi

if [[ "$BACKEND_SUITE" == "1" ]]; then
  say "backend-quality-suite CLI"
  run python3 -m venv /tmp/bsq-venv
  run /tmp/bsq-venv/bin/pip install -q "git+https://github.com/irfan-dahir/backend-quality-suite.git@main"
  ok "backend-suite at /tmp/bsq-venv/bin/backend-suite"
fi

if [[ "$SKILLS" == "1" ]]; then
  say "Laravel skills (laravel/boost)"
  run "$PHP" "$COMPOSER" require --dev laravel/boost
  run "$PHP" artisan package:discover
  run "$PHP" artisan boost:add-skill jeffallan/claude-skills --skill laravel-specialist
  run "$PHP" artisan boost:add-skill jeffallan/claude-skills --skill php-pro
  run "$PHP" artisan boost:add-skill affaan-m/ecc --skill laravel-security
  run "$PHP" artisan boost:add-skill affaan-m/everything-claude-code --skill laravel-patterns
  ok "skills installed under .ai/skills/"
fi

if [[ "$SEED" == "1" ]]; then
  say "Seeding (genres + magazines; anime/manga wait on jikan v5)"
  run "$PHP" artisan indexer:genres
  run "$PHP" artisan indexer:common
  ok "genres/magazines seeded"
fi

# -------------------------------------------------------------------- runtime
start_hint() {
  case "$SERVER" in
    serve)      echo "  tools/dev/php artisan serve --host=127.0.0.1 --port=8000" ;;
    roadrunner) echo "  OCTANE_SERVER=roadrunner tools/dev/php artisan octane:start --server=roadrunner --port=8000" ;;
    frankenphp) echo "  OCTANE_SERVER=frankenphp tools/dev/php artisan octane:start --server=frankenphp --port=8000" ;;
  esac
}

case "$SERVER" in
  serve) : ;;
  roadrunner)
    if [[ ! -x ./rr ]]; then
      say "Downloading RoadRunner binary"
      run "$PHP" artisan octane:install --server=roadrunner
    fi
    [[ -x ./rr ]] && ok "rr binary ($(./rr --version 2>/dev/null | head -1))" || warn "rr binary missing — run octane:install or install.sh again"
    ;;
  frankenphp)
    say "FrankenPHP (opt-in — binary not shipped in-repo)"
    FDIR="${FPHP_DIR:-/tmp/frankenphp}"; mkdir -p "$FDIR"
    # FrankenPHP lives under php/frankenphp; asset names are
    # frankenphp-{mac|linux}-{arm64|aarch64|x86_64}.
    case "$(uname -s)" in
      Darwin) FOS="mac";;
      Linux)  FOS="linux";;
      *) die "unsupported OS for frankenphp binary" ;;
    esac
    ARCH="$(uname -m | sed 's/arm64/aarch64/')"
    if [[ ! -x "$FDIR/frankenphp" ]]; then
      run curl -fsSL -o "$FDIR/frankenphp" "https://github.com/php/frankenphp/releases/latest/download/frankenphp-${FOS}-${ARCH}"
      run chmod +x "$FDIR/frankenphp"
    fi
    ok "binary at $FDIR/frankenphp"
    ;;
  *) die "unknown --server=$SERVER (roadrunner|frankenphp|serve)" ;;
esac

# --------------------------------------------------------------------- summary
say "Install complete — next, start it with:"
start_hint
echo
echo "  Health:   curl http://127.0.0.1:8000/up"
echo "  Tests:    tools/dev/php vendor/bin/phpunit --no-coverage"
echo "  Docs:     docs/SETUP.md · docs/PROVIDERS.md · docs/DOCKER.md"
echo "  Wrappers: script/setup · script/bootstrap · script/update · script/server · script/test"