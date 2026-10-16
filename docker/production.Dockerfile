FROM php:8.4-fpm-alpine AS php-base

RUN apk add --no-cache \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        oniguruma \
        postgresql-libs \
        sqlite-libs \
    && apk add --no-cache --virtual .build-deps \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        sqlite-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd mbstring opcache pdo_pgsql pdo_sqlite zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/uploads.ini $PHP_INI_DIR/conf.d/uploads.ini


FROM php-base AS dependencies

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts


FROM php-base AS frontend

RUN apk add --no-cache nodejs npm

COPY . .
COPY --from=dependencies /var/www/html/vendor ./vendor

RUN APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    composer dump-autoload --optimize --no-dev \
    && APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
    php artisan wayfinder:generate --with-form --no-interaction \
    && npm ci \
    && npm run build


FROM php-base AS app

COPY . .
COPY --from=dependencies /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/build ./public/build
COPY --from=frontend /var/www/html/bootstrap/cache ./bootstrap/cache
COPY docker/production-entrypoint.sh /usr/local/bin/production-entrypoint

RUN rm -f public/hot bootstrap/cache/config.php bootstrap/cache/events.php \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && ln -snf /var/www/html/storage/app/public public/storage \
    && chown -R www-data:www-data bootstrap/cache storage \
    && chmod +x /usr/local/bin/production-entrypoint

ENV APP_ENV=production \
    APP_DEBUG=false

ENTRYPOINT ["production-entrypoint"]
CMD ["php-fpm"]


FROM nginx:1.27-alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

RUN mkdir -p /var/www/html/storage
