# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# jikan-rest — Laravel 13 on Octane (RoadRunner), PHP 8.4 (Alpine)
#
# Replaces the old php:8.1.27-bullseye + RoadRunner 2.12.3 image, which could
# not satisfy the project's composer platform requirements (php ^8.3,
# mongodb/laravel-mongodb ^5 needs the modern MongoDB driver).
#
#   - Base: php:8.4-cli-alpine — Octane/RoadRunner workers are *CLI* processes,
#     not php-fpm, so the CLI variant is the correct runtime (a float on the
#     8.4 line; composer.json pins "config.platform.php" to 8.4.26 so the lock
#     resolves identically regardless of the container's patch level).
#   - ext-mongodb via pecl (recent stable), plus pcntl + sockets + opcache.
#     ext-json is compiled in on PHP 8.4; ctype/curl/mbstring/openssl/etc. are
#     bundled in the official Alpine images.
#   - RoadRunner binary 2025.1.15 — the current stable that spiral/roadrunner-cli
#     (dev requirement ^2.7) downloads through `php artisan octane:install`
#     (vendor/bin/rr get-binary), and >= Octane's required 2023.3.0.
#   - config:cache runs at container start (after .env/secrets are prepared) so
#     per-deployment env vars are honoured; route:cache is baked at build time
#     (verified: closures are serialisable on Laravel 13 and the DISABLE_USER_LISTS
#     user-list routes are cached correctly — keep DISABLE_USER_LISTS=false, or
#     warm the route cache with your target value).
#   - HEALTHCHECK hits the app root (there is no /up route yet) on the
#     octane/http port, which returns 200 JSON incl. the source-heartbeat block.
# ---------------------------------------------------------------------------

#################################
# 1) Composer dependencies      #
#################################
FROM composer:2 AS vendor
WORKDIR /app
# Copy the manifests first so dependency layers are cached across builds.
COPY composer.json composer.lock ./
# --no-scripts keeps post-root-package-install from copying .env (the entrypoint
# prepares .env from .env.dist at startup). --no-autoloader: dumped later.
RUN composer install -n --no-dev --no-interaction --no-progress \
    --no-scripts --no-autoloader --prefer-dist

#################################
# 2) RoadRunner server binary   #
#################################
# Version 2025.1.15 (see header). Pick the asset matching the build platform by
# setting --build-arg RR_STAGE=rr-arm64 when cross-building; default amd64.
FROM scratch AS rr-amd64
ADD https://github.com/roadrunner-server/roadrunner/releases/download/v2025.1.15/roadrunner-2025.1.15-linux-amd64.tar.gz /rr.tar.gz

FROM scratch AS rr-arm64
ADD https://github.com/roadrunner-server/roadrunner/releases/download/v2025.1.15/roadrunner-2025.1.15-linux-arm64.tar.gz /rr.tar.gz

#################################
# 3) Application image          #
#################################
FROM php:8.4-cli-alpine AS app

# --- PHP extensions ---------------------------------------------------------
# $PHPIZE_DEPS is provided by the official image (build toolchain for pecl).
# Requests from the app are served by Octane workers; opcache+JIT are tuned for
# a long-lived CLI worker.
RUN set -eux \
    && apk add --no-cache git curl ca-certificates $PHPIZE_DEPS \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pcntl sockets opcache \
    && apk del --purge $PHPIZE_DEPS

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.jit_buffer_size=32M'; \
        echo 'opcache.jit=1235'; \
    } > "${PHP_INI_DIR}/conf.d/zz-app-opcache.ini"

# --- supercronic (Laravel scheduler) ----------------------------------------
RUN set -eux \
    && ARCH="$(uname -m | sed 's/x86_64/amd64/; s/aarch64/arm64/')" \
    && wget -q "https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-${ARCH}" \
        -O /usr/bin/supercronic \
    && chmod +x /usr/bin/supercronic \
    && mkdir -p /etc/supercronic \
    && echo '*/1 * * * * php /app/artisan schedule:run' > /etc/supercronic/laravel

# --- Copy code + binary artifacts -------------------------------------------
WORKDIR /app
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ARG RR_STAGE=rr-amd64
COPY --from=${RR_STAGE} /rr.tar.gz /tmp/rr.tar.gz
RUN set -eux \
    && mkdir -p /tmp/rr \
    && tar -xzf /tmp/rr.tar.gz -C /tmp/rr --strip-components=1 \
    && mv /tmp/rr/rr /usr/local/bin/rr \
    && chmod +x /usr/local/bin/rr \
    && rm -rf /tmp/rr /tmp/rr.tar.gz

COPY --from=vendor /app/vendor /app/vendor
COPY . /app

# --- Optimize ----------------------------------------------------------------
# route:cache is safe on this app (Laravel 13 serialises the handful of closure
# routes; the DISABLE_USER_LISTS-gated user-list routes are baked in — keep the
# runtime value in sync with the build, i.e. false). config:cache is deferred to
# the entrypoint so runtime env vars (DB_HOST & co) apply to the cached config.
RUN set -eux \
    && composer dump-autoload -n --optimize --no-dev --classmap-authoritative \
    && php artisan route:cache

# --- Non-root user + storage ------------------------------------------------
RUN set -eux \
    && adduser -D -s /sbin/nologin -u 10001 -h /nonexistent jikanapi \
    && chown -R jikanapi:jikanapi /app \
    && chmod -R a+w storage/ bootstrap/cache \
    && chmod +x /app/docker-entrypoint.sh

USER jikanapi:jikanapi

EXPOSE 8080
EXPOSE 2114

# The app has no /up route; health-check the root endpoint (200 JSON, includes
# myanimelist_heartbeat). Requires MongoDB to be reachable from the container —
# the same requirement as serving real traffic.
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:8080/ || exit 1

ENTRYPOINT ["/app/docker-entrypoint.sh"]