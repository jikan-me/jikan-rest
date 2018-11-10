# nekomata1037/jikan-rest-web:YYY-MM-DD
FROM nekomata1037/jikan-rest-php:2018-11-08

RUN apt-get update && \
    apt-get install --no-install-recommends --no-install-suggests -y \
        ca-certificates \
        nginx \
        supervisor && \
    # Route nginx logs to syslog socket (will show in Docker logs)
    sed -i 's!/var/log/nginx/access.log!syslog:server=unix:/dev/log!g' /etc/nginx/nginx.conf && \
    sed -i 's!/var/log/nginx/error.log!syslog:server=unix:/dev/log!g' /etc/nginx/nginx.conf && \
    # Cleanup
    apt-get remove -y && \
    apt-get autoremove -y && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

ADD supervisor/php-fpm.conf /etc/supervisor/conf.d/php-fpm.conf
ADD supervisor/nginx.conf /etc/supervisor/conf.d/nginx.conf
ADD nginx.conf /etc/nginx/sites-available/default

ADD entrypoint.sh /entrypoint.sh
RUN chmod 700 /entrypoint.sh

WORKDIR /var/www

ENTRYPOINT ["/entrypoint.sh"]