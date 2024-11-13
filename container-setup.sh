#!/bin/bash

_JIKAN_API_VERSION=v4.0.0
SUBSTITUTE_VERSION=$_JIKAN_API_VERSION
if [ -x "$(command -v git)" ]; then
  # check if we have checked out a tag or not
  git symbolic-ref HEAD &> /dev/null
  if [ $? -ne 0 ]; then
    # if a tag is checked out then use the tag name as the version
    SUBSTITUTE_VERSION=$(git describe --tags)
  else
    # this is used when building locally
    SUBSTITUTE_VERSION=$(git describe --tags | sed -e "s/-[a-z0-9]\{8\}/-$(git rev-parse --short HEAD)/g")
  fi
fi
# set JIKAN_API_VERSION env var to "latest" or a tag which exists in the container registry to use the remote image
# otherwise docker-compose will look for a locally builded image
export _JIKAN_API_VERSION=${JIKAN_API_VERSION:-$SUBSTITUTE_VERSION}

DOCKER_COMPOSE_PROJECT_NAME=jikan-api
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
  echo "stop                   Stop Jikan API"
  echo "validate-prereqs       Validate pre-reqs installed (docker, docker-compose)"
  echo "execute-indexers       Execute the indexers, which will scrape and index data from MAL. (Notice: This can take days)"
  echo "index-incrementally    Executes the incremental indexers for each media type. (anime, manga)"
  echo ""
}

validate_prereqs() {
  docker_exists=$(command -v docker)
  docker_compose_exists=$(command -v docker-compose)
  podman_exists=$(command -v podman)
  podman_compose_exists=$(command -v podman-compose)

   if [ ! -x "$docker_exists" ] && [ ! -x "$podman_exists" ]; then
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

   if [ ! -x "$docker_compose_exists" ] && [ ! -x "$podman_compose_exists" ]; then
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
  $DOCKER_CMD inspect jikanme/jikan-rest:"$_JIKAN_API_VERSION" &> /dev/null && $DOCKER_CMD rmi jikanme/jikan-rest:"$_JIKAN_API_VERSION"
  $DOCKER_CMD build --rm --compress -t jikanme/jikan-rest:"$_JIKAN_API_VERSION" .
  $DOCKER_CMD tag jikanme/jikan-rest:"$_JIKAN_API_VERSION" jikanme/jikan-rest:latest
}

ensure_secrets() {
  declare -a secrets=("db_password" "db_admin_password" "redis_password" "typesense_api_key")

  if [ ! -f "db_username.txt" ]; then
    echo "db_username.txt not found, please provide a db_username [default is jikan]:"
    read -r db_username
    if [ -z "$db_username" ]; then
      db_username="jikan"
    fi
    echo -n "$db_username" > "db_username.txt"
  else
    echo -e "db_username.txt found, using it's value. \xE2\x9C\x94"
  fi

  if [ ! -f "db_admin_username.txt" ]; then
    echo "db_admin_username.txt not found, please provide a db_admin_username [default is jikan_admin]:"
    read -r db_admin_username
    if [ -z "$db_admin_username" ]; then
      db_admin_username="jikan_admin"
    fi
    echo -n "$db_admin_username" > "db_admin_username.txt"
  else
    echo -e "db_admin_username.txt found, using it's value. \xE2\x9C\x94"
  fi

  for secret_name in "${secrets[@]}"
  do
    if [ ! -f "$secret_name.txt" ]; then
      if [ "$secret_name" == "db_username" ]; then
        generated_secret="jikan"
      else
        generated_secret=$(LC_ALL=c tr -dc 'A-Za-z0-9!'\''()*+,-;<=>_' </dev/urandom | head -c 16  ; echo)
      fi
      echo "$secret_name.txt not found, please provide a $secret_name [default is $generated_secret]:"
      # prompt for secret and save it in file
      read -r secret_value
      if [ -z "$secret_value" ]; then
        secret_value=$generated_secret
      fi
      echo -n "$secret_value" > "$secret_name.txt"
    else
      echo -e "$secret_name.txt found, using it's value. \xE2\x9C\x94"
    fi
  done
}

start() {
   # todo: create a marker file for initial startup, and on initial startup ask the user whether they want a local image or the remote one
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
   "index-incrementally")
      echo "Indexing..."
      $DOCKER_COMPOSE_CMD -p "$DOCKER_COMPOSE_PROJECT_NAME" exec jikan_rest php /app/artisan indexer:incremental anime manga
      echo "Indexing done!"
      ;;
   *)
      echo "No command specified, displaying help"
      display_help
      ;;
esac
