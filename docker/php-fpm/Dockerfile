FROM php:7.3-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends zip zlib1g-dev libzip-dev libxml2-dev libpng-dev libghc-curl-dev libldb-dev libldap2-dev postgresql postgresql-client libpq-dev -yqq && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) pgsql pdo_pgsql zip xml intl mbstring gd curl bcmath opcache ldap && \
    docker-php-ext-enable apcu pdo_pgsql sodium ldap && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* &&\
    pecl install xdebug-2.9.8 && docker-php-ext-enable xdebug

COPY --from=composer /usr/bin/composer /usr/bin/composer

COPY others.ini /etc/php7.3/conf.d/

WORKDIR /var/www

EXPOSE 9000
