# Roadrunner Dev Dockerfile
FROM php:8.1-alpine

RUN apk add --no-cache autoconf openssl-dev g++ make pcre-dev icu-dev zlib-dev libzip-dev postgresql-dev && \
    docker-php-ext-install bcmath intl opcache zip sockets pdo_pgsql && \
    apk del --purge autoconf g++ make

WORKDIR /usr/src/app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock symfony.lock ./

RUN composer install --no-scripts --no-progress --no-interaction

RUN ./vendor/bin/rr get-binary --location /usr/local/bin

ENV APP_ENV=dev

EXPOSE 8080

USER root
COPY ./dockerfiles/roadrunner/dev-entrypoint.sh /root/entrypoint.sh
RUN chmod 544 /root/entrypoint.sh

CMD ["/root/entrypoint.sh"]