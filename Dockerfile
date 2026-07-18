FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock artisan ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY lang ./lang
COPY public ./public
COPY resources ./resources
COPY routes ./routes

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader


FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build


FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        opcache \
        pdo_sqlite \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build
COPY .env.example /var/www/html/.env

RUN mkdir -p \
        bootstrap/cache \
        database \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 8000

CMD ["sh", "-c", "php artisan key:generate --force && php artisan migrate --force && php artisan app:init && php artisan serve --host=0.0.0.0 --port=8000"]
