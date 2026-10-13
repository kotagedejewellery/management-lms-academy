FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        nodejs \
        npm \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-install -j"$(nproc)" mbstring opcache pdo_pgsql pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist --no-scripts
