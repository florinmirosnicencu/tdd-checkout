FROM php:7.4.16-fpm-buster

RUN apt-get update && apt-get install -y apt-utils ldap-utils zip unzip zlib1g-dev libpng-dev libzip-dev libldb-dev libldap2-dev git libxml2-dev && \
                          rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip pdo_mysql gd soap pcntl

COPY --from=composer:1.10.21 /usr/bin/composer /usr/bin/composer

RUN pecl install xdebug-2.9.8
RUN pecl install uopz
RUN docker-php-ext-enable xdebug

WORKDIR /var/www
