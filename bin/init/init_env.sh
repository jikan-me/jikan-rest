#!/usr/bin/env bash

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

. "${ROOT}"/bin/lib/env.sh
. "${ROOT}"/bin/lib/colors.sh
. "${ROOT}"/bin/lib/exitCheck.sh

DIR="${ROOT}/$1"

echo "Checking $1 .env file..."
if [ ! -e "${DIR}"/.env ]; then
  echo "No $1 .env file found. Creating from $1 .env.example..."
  cp "${DIR}"/.env.example "${DIR}"/.env
  exitCheck $?
  echo "NOTE: Please review the .env file and configure any required parameters."
else
  DIFF=$(diff "${DIR}"/.env "${DIR}"/.env.example)
  if [ `printf "${DIFF}" | wc -l` == 0 ]; then
    echo ".env file exists, and matches example. Skipping create."
  else
    printf "${RED}.env file exists, but does NOT match example. Skipping create.\n"
    printf "${RED}NOTE: Please note the following differences, and update your .env if necessary:\n"
    printf "${DIFF}"
    printf "${NC}\n"
  fi
fi
