#!/bin/bash
set -eo pipefail

if [[ $# -eq 0 ]] ; then
  exec php /app/docker-entrypoint.php
else
  exec php /app/docker-entrypoint.php "$@"
fi

exec rr serve -c .rr.yaml
