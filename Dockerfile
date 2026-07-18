# ==========================================
# Stage 1 - Composer
# ==========================================
FROM php:8.3-cli AS vendor

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libicu-dev \
    libsqlite3-dev \
    libzip-dev \
    && docker-php-ext-install \
        intl \
        pdo_sqlite \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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
php artisan config:clear && \
php artisan package:discover && \
php artisan migrate --force && \
php artisan cache:clear && \
php artisan app:init && \
php artisan serve --host=0.0.0.0 --port=\$PORT"
