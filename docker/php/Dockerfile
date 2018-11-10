# nekomata1037/jikan-rest-php:YYYY-MM-DD
FROM chekote/php:7.1.16-laravel5.6

RUN apt-get update && \
    apt-get install --no-install-recommends --no-install-suggests -o Dpkg::Options::="--force-confold" -y \
    #
    # jikan requirements
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-gd && \
    #
    # Cleanup
    apt-get remove -y && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*
