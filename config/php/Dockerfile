FROM php:8.3.8-fpm

ARG UID=1000
ARG GID=1000

WORKDIR /var/www/project

RUN apt-get update \
    && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && apt-get install -y sqlite3 \
    && apt-get install -y procps \
    && rm -rf /var/lib/apt/lists/* \
    && groupmod -g "${GID}" www-data && usermod -u "${UID}" -g "${GID}" www-data \
    && chown www-data:www-data -R /var/www

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data
