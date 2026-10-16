FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        nodejs \
        npm \
        libpng-dev \
        libzip-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd mbstring opcache pdo_pgsql pdo_sqlite zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/uploads.ini $PHP_INI_DIR/conf.d/uploads.ini

COPY composer.json composer.lock ./

RUN composer install --no-interaction --prefer-dist --no-scripts
