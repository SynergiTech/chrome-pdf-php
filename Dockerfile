ARG PHP_VERSION=8.0
FROM php:$PHP_VERSION-cli-alpine

RUN apk add git zip unzip autoconf make g++

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /package

RUN adduser -D -g '' dev

RUN chown dev -R /package

USER dev

COPY --chown=dev composer.json ./

ARG SYMFONY_PROCESS=5
RUN composer require symfony/process ^$SYMFONY_PROCESS.0

RUN composer install

COPY --chown=dev . .

RUN composer test
