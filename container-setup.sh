#!/bin/bash

JIKAN_API_VERSION=v4.0.0-rc.11
DOCKER_COMPOSE_PROJECT_NAME=jikan-api-$JIKAN_API_VERSION

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
  echo "validate-prereqs       Validate pre-reqs installed (docker, docker-compose)"
  echo "execute-indexers       Execute the indexers, which will scrape and index data from MAL. (Notice: This can take days)"
  echo ""
}

validate_prereqs() {
   docker -v >/dev/null 2>&1
   if [ $? -ne 0 ]; then
      echo -e "'docker' is not installed or not runnable without sudo. \xE2\x9D\x8C"
   else
      echo -e "Docker is Installed. \xE2\x9C\x94"
   fi

   docker-compose -v >/dev/null 2>&1
   if [ $? -ne 0 ]; then
      echo -e "'docker-compose' is not installed. \xE2\x9D\x8C"
   else
      echo -e "Docker compose is Installed. \xE2\x9C\x94"
   fi
}

build_image() {
   docker build --rm --compress -t jikanme/jikan-rest:$JIKAN_API_VERSION .
}

start() {
   docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME up -d
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
    "execute-indexers")
      echo "Indexing anime..."
      docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME exec jikan_rest php /app/artisan indexer:anime
      echo "Indexing manga..."
      docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME exec jikan_rest php /app/artisan indexer:manga
      echo "Indexing characters and people..."
      docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME exec jikan_rest php /app/artisan indexer:common
      echo "Indexing genres..."
      docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME exec jikan_rest php /app/artisan indexer:genres
      echo "Indexing producers..."
      docker-compose -p $DOCKER_COMPOSE_PROJECT_NAME exec jikan_rest php /app/artisan indexer:producers
      echo "Indexing done!"
      ;;
   *)
      echo "No command specified, displaying help"
      display_help
      ;;
esac
