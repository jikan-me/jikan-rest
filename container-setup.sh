#!/bin/bash

_JIKAN_API_VERSION=v4.0.0
SUBSTITUTE_VERSION=$_JIKAN_API_VERSION
if [ -x "$(command -v git)" ]; then
  SUBSTITUTE_VERSION=$(git describe --tags | sed -e "s/-[a-z0-9]\{8\}/-$(git rev-parse --short HEAD)/g")
fi
export _JIKAN_API_VERSION=${JIKAN_API_VERSION:-$SUBSTITUTE_VERSION}

DOCKER_COMPOSE_PROJECT_NAME=jikan-api-$_JIKAN_API_VERSION
DOCKER_CMD="docker"
DOCKER_COMPOSE_CMD="docker-compose"

display_help() {
  echo "============================================================"
  echo "Jikan API Container Setup CLI"
  echo "============================================================"
  echo "Syntax: ./container-setup.sh [command]"
  echo "Jikan API Version: $_JIKAN_API_VERSION"
  echo "---commands---"
  echo "help                   Print CLI help"
  echo "build-image            Build Image Locally"
  echo "start                  Start Jikan API (mongodb, typesense, redis, jikan-api workers)"
  echo "validate-prereqs       Validate pre-reqs installed (docker, docker-compose)"
  echo "execute-indexers       Execute the indexers, which will scrape and index data from MAL. (Notice: This can take days)"
  echo ""
}

validate_prereqs() {
  docker_exists=$(command -v docker)
  docker_compose_exists=$(command -v docker-compose)
  podman_exists=$(command -v podman)
  podman_compose_exists=$(command -v podman-compose)

   if [ -x "$docker_exists" ] && [ -x "$podman_exists" ]; then
      echo -e "'docker' is not installed. \xE2\x9D\x8C"
      exit 1
   else
      echo -e "Docker is Installed. \xE2\x9C\x94"
   fi

   if [ -x "$docker_exists" ]; then
      DOCKER_CMD="docker"
      docker -v >/dev/null 2>&1
      if [ $? -ne 0 ]; then
         echo -e "'docker' is not executable without sudo. \xE2\x9D\x8C"
         exit 1
      fi
   elif [ -n "$podman_exists" ]; then
      DOCKER_CMD="podman"
   fi

   if [ -x "$docker_compose_exists" ] && [ -x "$docker_compose_exists" ]; then
       echo -e "'docker-compose' is not installed. \xE2\x9D\x8C"
       exit 1
    else
       echo -e "Docker compose is Installed. \xE2\x9C\x94"
    fi

    if [ -x "$docker_compose_exists" ]; then
       DOCKER_COMPOSE_CMD="docker-compose"
    elif [ -x "$podman_compose_exists" ]; then
       DOCKER_COMPOSE_CMD="podman-compose"
    else
      echo "Error"
      exit 1
    fi
}

build_image() {
  validate_prereqs
  $DOCKER_CMD build --rm --compress -t jikanme/jikan-rest:"$_JIKAN_API_VERSION" .
  $DOCKER_CMD tag jikanme/jikan-rest:"$_JIKAN_API_VERSION" jikanme/jikan-rest:latest
}

ensure_secrets() {
  declare -a secrets=("db_password" "db_username" "redis_password" "typesense_api_key")

  for secret_name in "${secrets[@]}"
  do
    if [ ! -f "$secret_name.txt" ]; then
      if [ "$secret_name" == "db_username" ]; then
        generated_secret="jikan"
      else
        generated_secret=$(LC_ALL=c tr -dc 'A-Za-z0-9!"#$%&'\''()*+,-./:;<=>?@[\]^_{|}~' </dev/urandom | head -c 16  ; echo)
      fi
      echo "$secret_name.txt not found, please provide a $secret_name [default is $generated_secret]:"
      # prompt for secret and save it in file
      read -r secret_value
      if [ -z "$secret_value" ]; then
        secret_value=$generated_secret
      fi
      echo "$secret_value" > "$secret_name.txt"
    else
      echo -e "$secret_name.txt found, using it's value. \xE2\x9C\x94"
    fi
  done
}

start() {
  validate_prereqs
  ensure_secrets
  exec $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" up -d
}

case "$1" in
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
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" down
      ;;
    "execute-indexers")
      echo "Indexing anime..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:anime
      echo "Indexing manga..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:manga
      echo "Indexing characters and people..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:common
      echo "Indexing genres..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:genres
      echo "Indexing producers..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:producers
      echo "Indexing done!"
      ;;
   *)
      echo "No command specified, displaying help"
      display_help
      ;;
esac
