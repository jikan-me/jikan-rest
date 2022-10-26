#!/bin/bash
set -eo pipefail

status=0
if [[ $# -eq 0 ]] ; then
  php /app/docker-entrypoint.php
  status=$?
else
  php /app/docker-entrypoint.php "$@"
  status=$?
fi

[[ $status -ne 0 ]] && exit $status

exec rr serve -c .rr.yaml
