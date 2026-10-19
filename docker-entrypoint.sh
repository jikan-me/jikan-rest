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

# Laravel's encrypter needs APP_KEY. If none was supplied (container env or
# APP_KEY__FILE secret, both of which end up in .env/env), generate one for this
# container. Set APP_KEY explicitly in production so it is stable across restarts.
if [ -z "${APP_KEY:-}" ] ; then
  # an *empty* APP_KEY in the environment would shadow the value in .env
  unset APP_KEY
  if ! grep -Eq '^APP_KEY=.+' /app/.env 2>/dev/null ; then
    php /app/artisan key:generate --force
  fi
fi

# Cache config now that runtime env + (optionally) injected secrets are in the
# .env file — baking it at image build time would freeze per-deployment values.
php /app/artisan config:cache

# Run the Laravel scheduler in the background (supercronic).
if [ -f /etc/supercronic/laravel ] ; then
  /usr/bin/supercronic /etc/supercronic/laravel &
  SUPERVISOR_PID=$!
  trap 'kill "$SUPERVISOR_PID" 2>/dev/null || true' TERM INT EXIT
fi

# Optional queue workers (replaces the old RoadRunner `laravel_queue_worker_1`
# service). Set QUEUE_WORKERS=N to run N `queue:work` processes.
queue_workers="${QUEUE_WORKERS:-0}"
i=0
while [ "$i" -lt "$queue_workers" ]; do
  php /app/artisan queue:work --sleep=3 --tries=3 &
  i=$((i + 1))
done

# Octane (RoadRunner) generates its own .rr.yaml on start and overrides the
# important bits via -o flags; drop any legacy v3 config that was shipped.
rm -f /app/.rr.yaml

# A custom RoadRunner config can be supplied via OCTANE_RR_CONFIG.
if [ -n "${OCTANE_RR_CONFIG:-}" ] ; then
  set -- --rr-config="$OCTANE_RR_CONFIG" "$@"
fi

exec php /app/artisan octane:start --server=roadrunner --host=0.0.0.0 --port=8080 "$@"