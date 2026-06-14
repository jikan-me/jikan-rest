#!/bin/bash

set -euo pipefail

_JIKAN_API_VERSION=v4.0.0
SUBSTITUTE_VERSION=$_JIKAN_API_VERSION
if [ -x "$(command -v git)" ]; then
  # check if we have checked out a tag or not
  if ! git symbolic-ref HEAD &>/dev/null; then
    # if a tag is checked out then use the tag name as the version
    SUBSTITUTE_VERSION=$(git describe --tags 2>/dev/null || echo "$_JIKAN_API_VERSION")
  else
    # this is used when building locally
    SUBSTITUTE_VERSION=$(git describe --tags 2>/dev/null \
      | sed -e "s/-[a-z0-9]\{8\}/-$(git rev-parse --short HEAD)/g" \
      || echo "$_JIKAN_API_VERSION")
  fi
fi
# set JIKAN_API_VERSION env var to "latest" or a tag which exists in the
# container registry to use the remote image
# otherwise docker compose will look for a locally built image
export JIKAN_API_VERSION=${JIKAN_API_VERSION:-$SUBSTITUTE_VERSION}
DOCKER_COMPOSE_PROJECT_NAME=jikan-api
DOCKER_CMD="docker"
DOCKER_COMPOSE_CMD=(docker compose)

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
  if ! command -v docker >/dev/null 2>&1 && ! command -v podman >/dev/null 2>&1; then
    printf "'docker' or 'podman' is not installed. ❌\n"
    exit 1
  fi

  if command -v docker >/dev/null 2>&1; then
    DOCKER_CMD="docker"
    if ! docker -v >/dev/null 2>&1; then
      printf "'docker' is not executable without sudo. ❌\n"
      exit 1
    fi
    if docker compose version >/dev/null 2>&1; then
      DOCKER_COMPOSE_CMD=(docker compose)
      printf "Docker Compose is Installed. ✔\n"
    else
      printf "'docker compose' plugin is not installed. ❌\n"
      exit 1
    fi
  elif command -v podman >/dev/null 2>&1; then
    DOCKER_CMD="podman"
    if ! podman -v >/dev/null 2>&1; then
      printf "'podman' is not executable without sudo. ❌\n"
      exit 1
    fi
    if podman compose version >/dev/null 2>&1; then
      DOCKER_COMPOSE_CMD=(podman compose)
      printf "Podman Compose is Installed. ✔\n"
    else
      printf "'podman compose' is not available. ❌\n"
      exit 1
    fi
  fi
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

ensure_username_secret() {
  local file="$1"
  local default_value="$2"
  local value

  if [ ! -f "$SECRETS_DIR/$file" ]; then
    echo "$file not found, please provide a value [default is $default_value]:"
    read -r value || true
    if [ -z "$value" ]; then
      value="$default_value"
    fi
    echo -n "$value" > "$SECRETS_DIR/$file"
  else
    printf '%s found, using its value. ✔\n' "$file"
  fi
}

ensure_secrets() {
  local SECRETS_DIR="${SECRETS_DIR:-.}"

  declare -a secrets=("db_password" "db_admin_password" "redis_password" "typesense_api_key")

  ensure_username_secret "db_username.txt" "jikan"
  ensure_username_secret "db_admin_username.txt" "jikan_admin"

  for secret_name in "${secrets[@]}"
  do
    if [ ! -f "$SECRETS_DIR/$secret_name.txt" ]; then
      generated_secret=$(LC_ALL=C tr -dc 'A-Za-z0-9!()*+,;<=>_-' </dev/urandom | head -c 16) || true
      echo "$secret_name.txt not found, please provide a $secret_name [default is $generated_secret]:"
      read -rs secret_value || true
      echo
      if [ -z "$secret_value" ]; then
        secret_value=$generated_secret
      fi
      echo -n "$secret_value" > "$SECRETS_DIR/$secret_name.txt"
    else
      printf '%s.txt found, using its value. ✔\n' "$secret_name"
    fi
  done
}

start() {
  # todo: create a marker file for initial startup, and on initial startup ask
  # the user whether they want a local image or the remote one
  validate_prereqs
  ensure_secrets
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
