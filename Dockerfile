# ==========================================
# Stage 1 - Composer
# ==========================================
FROM composer:2 AS vendor

WORKDIR /app

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# ==========================================
# Stage 2 - Node
# ==========================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

RUN npm run build

# ==========================================
# Stage 3 - Production
# ==========================================
FROM php:8.3-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_sqlite \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy application
COPY --from=vendor /app /var/www/html

# Copy built assets
COPY --from=assets /app/public/build ./public/build

# Create SQLite database
RUN mkdir -p database \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 10000

CMD sh -c "\
php artisan key:generate --force && \
php artisan migrate --force && \
php artisan app:init && \
php artisan serve --host=0.0.0.0 --port=\$PORT"