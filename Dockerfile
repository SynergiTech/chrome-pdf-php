ARG PHP_VERSION=8.0
FROM php:$PHP_VERSION-cli-alpine

RUN apk add git zip unzip autoconf make g++

RUN if [ -z "${PHP_VERSION##7\.1*}" ]; then \
    pecl install xdebug-2.9.8 && docker-php-ext-enable xdebug; \
else \
    pecl install xdebug && docker-php-ext-enable xdebug; \
fi;

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /package

COPY composer.json ./

ARG SYMFONY_PROCESS=5
RUN composer require symfony/process ^$SYMFONY_PROCESS.0

RUN composer install

COPY . .

RUN composer test
