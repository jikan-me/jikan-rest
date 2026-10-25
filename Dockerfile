# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# jikan-rest — Laravel 13 on Octane (Swoole), PHP 8.4 (Alpine)
#
# Replaces the old php:8.1.27-bullseye + RoadRunner 2.12.3 image, which could
# not satisfy the project's composer platform requirements (php ^8.4.4,
# mongodb/laravel-mongodb ^5 needs the modern MongoDB driver).
#
#   - Base: php:8.4.27-cli-alpine — Octane/Swoole workers are *CLI* processes,
#     not php-fpm, so the CLI variant is the correct runtime. Pinned to the
#     patch release (8.4.27 fixes GHSA-w8hw-w9fx-j79w / GHSA-6wmf-4rv5-p3rp) so
#     builds are reproducible; composer.json pins "config.platform.php" to the
#     same 8.4.27. Bump both together.
#   - ext-swoole, ext-mongodb and ext-brotli via pecl (recent stable; swoole is
#     the Octane server, brotli backs the CompressResponse middleware), plus
#     pcntl + sockets + opcache. Swoole is built with its PECL defaults
#     (openssl and brotli detected from the build deps; no curl/database
#     hooks). Octane runs Swoole with coroutines disabled, which suits the
#     non-hookable ext-mongodb. openssl-dev is a build-only dependency; it also
#     gives ext-mongodb TLS support (libssl3 is in the base image).
#     ext-json is compiled in on PHP 8.4; ctype/curl/mbstring/openssl/etc. are
#     bundled in the official Alpine images.
#   - config:cache runs at container start (after .env/secrets are prepared) so
#     per-deployment env vars are honoured; route:cache is baked at build time
#     (verified: closures are serialisable on Laravel 13 and the DISABLE_USER_LISTS
#     user-list routes are cached correctly — keep DISABLE_USER_LISTS=false, or
#     warm the route cache with your target value).
#   - HEALTHCHECK hits GET /up (routes/api.php — a bare 200 "OK" closure with no
#     DB dependency) on the octane/http port, so liveness only requires the
#     Octane worker to be serving, not MongoDB.
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
# --ignore-platform-reqs: the composer image has no ext-mongodb/pcntl/sockets/swoole;
# the runtime stage (which has them) does the authoritative dump-autoload.
RUN composer install -n --no-dev --no-interaction --no-progress \
    --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

#################################
# 2) Application image          #
#################################
FROM php:8.4.27-cli-alpine AS app

# --- PHP extensions ---------------------------------------------------------
# $PHPIZE_DEPS is provided by the official image (build toolchain for pecl).
# Requests from the app are served by Octane workers; opcache+JIT are tuned for
# a long-lived CLI worker.
RUN set -eux \
    && apk add --no-cache git curl ca-certificates brotli-libs libstdc++ \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers brotli-dev openssl-dev \
    && pecl install mongodb \
    && pecl install brotli \
    && yes '' | pecl install swoole \
    && docker-php-ext-enable mongodb brotli swoole \
    && docker-php-ext-install pcntl sockets opcache \
    && apk del --purge .build-deps \
    && rm -rf /tmp/pear

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

# --- Copy code -----------------------------------------------------------------
WORKDIR /app
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# --- Non-root user ------------------------------------------------------------
# Created before the code is copied so COPY --chown can set ownership in the same
# layer (a separate `chown -R /app` layer took ~65 s and duplicated /app, vendor
# included, in the image). /app itself must be owned by jikanapi: the entrypoint
# writes /app/.env at startup.
RUN set -eux \
    && adduser -D -s /sbin/nologin -u 10001 -h /nonexistent jikanapi \
    && chown jikanapi:jikanapi /app

COPY --chown=jikanapi:jikanapi --from=vendor /app/vendor /app/vendor
COPY --chown=jikanapi:jikanapi . /app

USER jikanapi:jikanapi

# --- Optimize + storage --------------------------------------------------------
# route:cache is safe on this app (Laravel 13 serialises the handful of closure
# routes; the DISABLE_USER_LISTS-gated user-list routes are baked in — keep the
# runtime value in sync with the build, i.e. false). config:cache is deferred to
# the entrypoint so runtime env vars (DB_HOST & co) apply to the cached config.
# Runs as jikanapi so the generated files (vendor/composer, bootstrap/cache) are
# owned by the runtime user. storage/ and bootstrap/cache stay world-writable so
# the image also works when run with an arbitrary --user.
RUN set -eux \
    && COMPOSER_HOME=/tmp/composer composer dump-autoload -n --optimize --no-dev --classmap-authoritative \
    && rm -rf /tmp/composer \
    && php artisan route:cache \
    && mkdir -p storage/framework/cache storage/framework/views storage/logs \
    && chmod -R a+w storage/ bootstrap/cache \
    && chmod +x /app/docker-entrypoint.sh

EXPOSE 8080

# GET /up (routes/api.php) returns a bare 200 "OK" with no DB dependency —
# verifies the Octane worker is serving without coupling readiness to MongoDB.
HEALTHCHECK --interval=30s --timeout=5s --start-period=15s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1:8080/up || exit 1

ENTRYPOINT ["/app/docker-entrypoint.sh"]