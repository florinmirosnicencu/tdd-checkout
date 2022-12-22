FROM php:8.2.0RC7-fpm-buster

RUN apt-get update &&  \
    apt-get install -y apt-utils \
     ldap-utils \
     zip  \
    unzip  \
    zlib1g-dev  \
    libpng-dev  \
    libzip-dev  \
    libldb-dev  \
    libldap2-dev  \
    git  \
    libxml2-dev && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure zip
RUN docker-php-ext-install zip pdo_mysql gd soap pcntl

COPY --from=composer:2.1.14 /usr/bin/composer /usr/bin/composer

RUN pecl install xdebug-3.1.2
RUN pecl install uopz-7.1.1
RUN docker-php-ext-enable xdebug

WORKDIR /var/www
