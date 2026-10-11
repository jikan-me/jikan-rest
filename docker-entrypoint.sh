#!/bin/sh
set -eo pipefail

status=0
if [ $# -eq 0 ] ; then
  php /app/docker-entrypoint.php
  status=$?
else
  php /app/docker-entrypoint.php "$@"
  status=$?
fi

[ $status -ne 0 ] && exit $status

# Cache config now that runtime env + (optionally) injected secrets are in the
# .env file — baking it at image build time would freeze per-deployment values.
php /app/artisan config:cache

# Run the Laravel scheduler in the background (supercronic).
if [ -f /etc/supercronic/laravel ] ; then
  /usr/bin/supercronic /etc/supercronic/laravel &
  SUPERVISOR_PID=$!
  trap 'kill "$SUPERVISOR_PID" 2>/dev/null || true' TERM INT EXIT
fi

# Octane (RoadRunner) generates its own .rr.yaml on start and overrides the
# important bits via -o flags; drop any legacy v3 config that was shipped.
rm -f /app/.rr.yaml

exec php /app/artisan octane:start --server=roadrunner --host=0.0.0.0 --port=8080 "$@"