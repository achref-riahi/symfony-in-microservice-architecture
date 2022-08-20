# Roadrunner Dev Dockerfile
FROM php:8.1-alpine

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        linux-headers \
    && apk add --update --no-cache \
        openssl-dev \
        pcre-dev \
        icu-dev \
        icu-data-full \
        libzip-dev \
        postgresql-dev \
        protobuf \
        grpc \
    && docker-php-ext-install  \
        bcmath \
        intl \
        opcache \
        zip \
        sockets \
        pdo_pgsql \
    && pecl install protobuf \
    && pecl install grpc \
    && docker-php-ext-enable \
        grpc \
        protobuf \
    && pecl clear-cache \
    && apk del --purge .build-deps

WORKDIR /usr/src/app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock symfony.lock ./

RUN composer install --no-scripts --no-progress --no-interaction

RUN ./vendor/bin/rr get-binary --location /usr/local/bin

ENV APP_ENV=dev

EXPOSE 8080 9000

USER root
COPY ./dockerfiles/roadrunner/dev-entrypoint.sh /root/entrypoint.sh
RUN chmod 544 /root/entrypoint.sh

CMD ["/root/entrypoint.sh"]