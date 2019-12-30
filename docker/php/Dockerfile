FROM php:7.1.33-fpm-buster

RUN apt-get update && apt-get install -y apt-utils ldap-utils zip unzip zlib1g-dev libpng-dev libzip-dev libldb-dev libldap2-dev git libxml2-dev && \
                          rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip pdo_mysql gd soap pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN pecl install xdebug-2.9.0
RUN pecl install uopz
RUN docker-php-ext-enable xdebug

COPY ./config/xdebug.ini .
RUN cat xdebug.ini >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && rm xdebug.ini

COPY ./config/php.ini /usr/local/etc/php/

WORKDIR /var/www
