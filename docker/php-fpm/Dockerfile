FROM php:8.2-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends iproute2 zip zlib1g-dev libzip-dev libxml2-dev libpng-dev libghc-curl-dev libldb-dev libldap2-dev postgresql postgresql-client libpq-dev -yqq && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) pgsql pdo_pgsql zip xml intl gd curl bcmath opcache ldap && \
    docker-php-ext-enable apcu pdo_pgsql sodium ldap && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* &&\
    pecl install xdebug-3.3.1 && docker-php-ext-enable xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# ENTRYPOINT [ "init.sh" ]

WORKDIR /var/www

EXPOSE 9000
