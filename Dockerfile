FROM spiralscout/roadrunner:2.10.6 as roadrunner
FROM composer:2.3.9 as composer
FROM mlocati/php-extension-installer:1.5.29 as php-ext-installer
FROM php:8.1-bullseye as runtime
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=php-ext-installer /usr/bin/install-php-extensions /usr/local/bin/
ENV COMPOSER_HOME="/tmp/composer"
RUN install-php-extensions gd exif intl bz2 gettext mongodb-stable redis opcache sockets pcntl

RUN	set -ex \
    && apt-get update && apt-get install -y --no-install-recommends \
	openssl \
	git \
	dos2unix \
	unzip \
  wget \
  # install supercronic (for laravel task scheduling), project page: <https://github.com/aptible/supercronic>
	&& wget -q "https://github.com/aptible/supercronic/releases/download/v0.1.12/supercronic-linux-amd64" \
	   -O /usr/bin/supercronic \
	&& chmod +x /usr/bin/supercronic \
	&& mkdir /etc/supercronic \
	&& echo '*/1 * * * * php /app/artisan schedule:run' > /etc/supercronic/laravel \
	&& rm -rf /var/lib/apt/lists/* \
	# enable opcache for CLI and JIT, docs: <https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit>
	&& echo -e "\nopcache.enable=1\nopcache.enable_cli=1\nopcache.jit_buffer_size=32M\nopcache.jit=1235\n" >> \
	    ${PHP_INI_DIR}/conf.d/docker-php-ext-opcache.ini \
	# show installed modules
	&& php -m \
	# create unpriviliged user
	&& adduser --disabled-password --shell "/sbin/nologin" --home "/nonexistent" --no-create-home --uid "10001" --gecos "" "jikanapi" \
	&& mkdir /app /var/run/rr \
	&& chown -R jikanapi:jikanapi /app /var/run/rr \
	&& chmod -R 777 /var/run/rr

# install roadrunner
COPY --from=roadrunner /usr/bin/rr /usr/bin/rr

USER jikanapi:jikanapi

WORKDIR /app

# copy composer (json|lock) files for dependencies layer caching
COPY --chown=jikanapi:jikanapi ./composer.* /app/

# install composer dependencies (autoloader MUST be generated later!)
RUN composer install -n --no-dev --no-cache --no-ansi --no-autoloader --no-scripts --prefer-dist

# copy application sources into image (completely)
COPY --chown=jikanapi:jikanapi . /app/

RUN set -ex \
    && composer update jikan-me/jikan \
    && composer dump-autoload -n --optimize \
    && chmod -R 777 ${COMPOSER_HOME}/cache \
    && chmod -R a+w storage/ \
    && chown -R jikanapi:jikanapi /app

EXPOSE 8080
EXPOSE 2114

HEALTHCHECK CMD curl --fail http://localhost:2114/health?plugin=http || exit 1

# unset default image entrypoint.
ENTRYPOINT ["/app/docker-entrypoint.sh"]
