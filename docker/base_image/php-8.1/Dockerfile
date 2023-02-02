FROM spiralscout/roadrunner:2.12.2 as roadrunner
FROM composer:2.5.1 as composer
FROM mlocati/php-extension-installer:1.5.52 as php-ext-installer
FROM php:8.1.13-bullseye as runtime
ARG GITHUB_PERSONAL_TOKEN
LABEL org.opencontainers.image.source=https://github.com/jikan-me/jikan-rest/docker/base_image/php-8.1
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=php-ext-installer /usr/bin/install-php-extensions /usr/local/bin/
ENV COMPOSER_HOME="/tmp/composer"
RUN set -x \
    && install-php-extensions gd exif intl bz2 gettext mongodb-stable redis opcache sockets pcntl \
    # install xdebug (for testing with code coverage), but do not enable it
    && IPE_DONT_ENABLE=1 install-php-extensions xdebug-3.2.0

# install roadrunner
COPY --from=roadrunner /usr/bin/rr /usr/bin/rr
