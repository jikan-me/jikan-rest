#!/bin/bash

set -euo pipefail

# Note: on macOS you might need to start podman/docker first before running this script
# anchor all relative paths (build context, secrets, marker) to the script dir.
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do
  DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
  SOURCE="$(readlink "$SOURCE")"
  [ "${SOURCE:0:1}" != "/" ] && SOURCE="$DIR/$SOURCE"
done
SCRIPT_DIR="$(cd -P "$(dirname "$SOURCE")" >/dev/null 2>&1 && pwd)"
cd "$SCRIPT_DIR"

_USER_JIKAN_API_VERSION=${JIKAN_API_VERSION:-}
_JIKAN_API_VERSION=v4.0.0
SUBSTITUTE_VERSION=$_JIKAN_API_VERSION
if [ -x "$(command -v git)" ]; then
  if ! git symbolic-ref HEAD &>/dev/null; then
    SUBSTITUTE_VERSION=$(git describe --tags 2>/dev/null || echo "$_JIKAN_API_VERSION")
  else
    # strip the leading 'g' from the abbreviated-hash suffix
    # (e.g. v4.0.0-12-g1a2b3c4 -> v4.0.0-12-1a2b3c4). Width-agnostic.
    SUBSTITUTE_VERSION=$(git describe --tags 2>/dev/null \
      | sed -e 's/-g\([0-9a-f]\{7,\}\)$/-\1/' \
      || echo "$_JIKAN_API_VERSION")
  fi
fi
export JIKAN_API_VERSION=${JIKAN_API_VERSION:-$SUBSTITUTE_VERSION}
DOCKER_COMPOSE_PROJECT_NAME=jikan-api
DOCKER_CMD="docker"
DOCKER_COMPOSE_CMD=(docker compose)
SECRETS_DIR="${SECRETS_DIR:-.}"
MARKER_FILE="${MARKER_FILE:-$SECRETS_DIR/.jikan-image-source}"

display_help() {
  echo "============================================================"
  echo "Jikan API Container Setup CLI"
  echo "============================================================"
  echo "Syntax: ./container-setup.sh [command]"
  echo "Jikan API Version: $JIKAN_API_VERSION"
  echo "---commands---"
  echo "help                   Print CLI help"
  echo "build-image            Build Image Locally"
  echo "start                  Start Jikan API (mongodb, typesense, redis, jikan-api workers)"
  echo "stop                   Stop Jikan API"
  echo "validate-prereqs       Validate pre-reqs installed (docker, docker compose)"
  echo "execute-indexers       Execute the indexers, which will scrape and index data from MAL. (Notice: This can take days)"
  echo "index-incrementally    Executes the incremental indexers for each media type. (anime, manga)"
  echo ""
}

validate_prereqs() {
  if command -v docker >/dev/null 2>&1 \
    && docker info >/dev/null 2>&1 \
    && docker compose version >/dev/null 2>&1; then
    DOCKER_CMD="docker"
    DOCKER_COMPOSE_CMD=(docker compose)
    printf "Docker Compose is installed. ✔\n"
    return 0
  fi

  if command -v podman >/dev/null 2>&1 \
    && podman info >/dev/null 2>&1 \
    && podman compose version >/dev/null 2>&1; then
    DOCKER_CMD="podman"
    DOCKER_COMPOSE_CMD=(podman compose)
    printf "Podman Compose is installed. ✔\n"
    return 0
  fi

  if ! command -v docker >/dev/null 2>&1 && ! command -v podman >/dev/null 2>&1; then
    printf "'docker' or 'podman' is not installed. ❌\n"
  elif command -v docker >/dev/null 2>&1 && ! docker info >/dev/null 2>&1; then
    printf "'docker' cannot reach the daemon (check permissions / is it running?). ❌\n"
  else
    printf "A container engine was found but its 'compose' command is unavailable. ❌\n"
  fi
  exit 1
}

build_image() {
  validate_prereqs
  if $DOCKER_CMD inspect jikanme/jikan-rest:"$JIKAN_API_VERSION" &>/dev/null; then
    if ! $DOCKER_CMD rmi jikanme/jikan-rest:"$JIKAN_API_VERSION"; then
      printf "Warning: failed to remove existing image (it may be in use). ❌\n"
    fi
  fi
  $DOCKER_CMD build --rm -t jikanme/jikan-rest:"$JIKAN_API_VERSION" .
  $DOCKER_CMD tag jikanme/jikan-rest:"$JIKAN_API_VERSION" jikanme/jikan-rest:latest
}

# URL-safe charset so secrets survive interpolation into connection strings/URIs.
generate_secret() {
  LC_ALL=C tr -dc 'A-Za-z0-9._~-' </dev/urandom | head -c 24 || true
}

# ensure_secret <file> <default_value> <silent: 0|1>
# silent=1 reads with no echo AND never prints the default (used for passwords).
ensure_secret() {
  local file="$1" default_value="$2" silent="$3" value confirm
  local path="$SECRETS_DIR/$file"

  if [ -f "$path" ]; then
    printf '%s found, using its value. ✔\n' "$file"
    return 0
  fi

  if [ "$silent" -eq 1 ]; then
    while true; do
      printf '%s not found. Press Enter to auto-generate a secure value, or type one:\n' "$file"
      read -rs value || true
      echo

      # Empty -> auto-generate, no confirmation needed.
      if [ -z "$value" ]; then
        value="$default_value"
        break
      fi

      printf 'Confirm value for %s:\n' "$file"
      read -rs confirm || true
      echo

      if [ "$value" = "$confirm" ]; then
        break
      fi
      printf 'Values did not match, please try again. ❌\n'
    done
  else
    printf '%s not found, please provide a value [default is %s]:\n' "$file" "$default_value"
    read -r value || true
    if [ -z "$value" ]; then
      value="$default_value"
    fi
  fi

  printf '%s' "$value" > "$path"
}

ensure_secrets() {
  # Usernames: showing the default is fine.
  ensure_secret "db_username.txt" "jikan" 0
  ensure_secret "db_admin_username.txt" "jikan_admin" 0

  # Passwords/keys: silent input, generated default never displayed.
  local secrets=("db_password" "db_admin_password" "redis_password" "typesense_api_key")
  local s
  for s in "${secrets[@]}"; do
    ensure_secret "$s.txt" "$(generate_secret)" 1
  done
}

ensure_image_source() {
  if [ ! -f "$MARKER_FILE" ]; then
    echo "Initial startup detected."
    echo "Use a [l]ocally built image or the [r]emote registry image? [l/r, default: r]:"
    read -r image_choice || true
    image_choice="$(printf '%s' "$image_choice" | tr '[:upper:]' '[:lower:]')"
    case "$image_choice" in
      l|local)
        echo "local" > "$MARKER_FILE"
        ;;
      *)
        echo "remote" > "$MARKER_FILE"
        ;;
    esac
  fi

  IMAGE_SOURCE=$(cat "$MARKER_FILE")
  printf 'Using %s image. ✔\n' "$IMAGE_SOURCE"

  if [ "$IMAGE_SOURCE" = "local" ]; then
    if ! $DOCKER_CMD inspect jikanme/jikan-rest:"$JIKAN_API_VERSION" &>/dev/null; then
      echo "Local image jikanme/jikan-rest:$JIKAN_API_VERSION not found, building it..."
      build_image
    fi
  else
    if [ -z "$_USER_JIKAN_API_VERSION" ]; then
      export JIKAN_API_VERSION=latest
    fi
  fi
}

start() {
  validate_prereqs
  ensure_secrets
  ensure_image_source
  "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" up -d
}

if [ "$#" -gt 1 ]; then
  echo "Error: too many arguments. Expected exactly one command."
  display_help
  exit 1
fi

case "${1:-}" in
  "help")
    display_help
    ;;
  "validate-prereqs")
    validate_prereqs
    ;;
  "build-image")
    build_image
    ;;
  "start")
    start
    ;;
  "stop")
    validate_prereqs
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" down
    ;;
  "execute-indexers")
    validate_prereqs
    ensure_secrets
    echo "Indexing anime..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:anime
    echo "Indexing manga..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:manga
    echo "Indexing characters and people..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:common
    echo "Indexing genres..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:genres
    echo "Indexing producers..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:producers
    echo "Indexing done!"
    ;;
  "index-incrementally")
    validate_prereqs
    ensure_secrets
    echo "Indexing..."
    "${DOCKER_COMPOSE_CMD[@]}" -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:incremental anime manga
    echo "Indexing done!"
    ;;
  "")
    echo "No command specified, displaying help"
    display_help
    ;;
  *)
    echo "Unknown command: $1"
    display_help
    exit 1
    ;;
esac
