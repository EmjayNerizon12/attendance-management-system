# Deploying Laravel 12 + Filament + SQLite to Render (Docker)

## Prerequisites

- Laravel 12
- Vue 3 + Vite
- Filament 4
- SQLite
- GitHub Repository
- Render Account

---

# 1. Prepare the Laravel Project

## 1.1 Create the SQLite Database

Do **not** commit `database.sqlite` to GitHub.

Create the file locally:

```bash
touch database/database.sqlite
```

Your project should contain:

```
database/
├── database.sqlite
├── migrations/
└── seeders/
```

---

## 1.2 Ignore SQLite Database

Add to `.gitignore`

```gitignore
/database/database.sqlite
```

If it was already committed:

```bash
git rm --cached database/database.sqlite
git add .gitignore
git commit -m "Ignore SQLite database"
git push
```

---

## 1.3 Local Environment

Example `.env`

```env
APP_NAME="Attendance Management System"
APP_ENV=local
APP_KEY=base64:YOUR_KEY
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

Run locally:

```bash
php artisan migrate
php artisan app:init
php artisan serve
```

Verify everything works.

---

# 2. Create Dockerfile

Create:

```
Dockerfile
```

Example:

```dockerfile
# ==========================================
# Stage 1 - Composer
# ==========================================
FROM php:8.3-cli AS vendor

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libsqlite3-dev \
    libzip-dev \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_sqlite \
        zip \
        opcache

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
        opcache

COPY --from=vendor /app /var/www/html

COPY --from=assets /app/public/build ./public/build

RUN mkdir -p /var/www/html/database \
    && touch /var/www/html/database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

EXPOSE 10000

CMD sh -c "\
php artisan optimize:clear && \
php artisan migrate --force && \
php artisan app:init && \
php artisan serve --host=0.0.0.0 --port=\$PORT"
```

---

# 3. Create .dockerignore

```text
.git
.github
vendor
node_modules
.env
storage/logs/*
storage/framework/cache/*
storage/framework/views/*
storage/framework/sessions/*
```

---

# 4. Push to GitHub

```bash
git add .
git commit -m "Prepare Render deployment"
git push origin main
```

---

# 5. Create Render Web Service

1. Login to Render
2. Click **New**
3. Select **Web Service**
4. Connect GitHub Repository
5. Render detects the Dockerfile
6. Choose Docker Environment
7. Create Service

---

# 6. Configure Environment Variables

Go to:

```
Render
└── Web Service
    └── Environment
```

Add:

```env
APP_NAME=Attendance Management System

APP_ENV=production
APP_DEBUG=false

APP_KEY=base64:YOUR_APP_KEY

APP_URL=https://your-app-name.onrender.com

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

Never commit your production `.env` file.

Render injects these variables when the container starts.

---

# 7. Deployment Flow

```
GitHub
    │
    ▼
Docker Build
(Dockerfile executes)
    │
    ▼
Docker Image
    │
    ▼
Render starts Container
    │
    ▼
Render injects Environment Variables
(APP_KEY, APP_URL, DB_*, etc.)
    │
    ▼
CMD executes
    │
    ▼
Laravel starts
```

---

# 8. Runtime Commands

The container executes:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan app:init
php artisan serve --host=0.0.0.0 --port=$PORT
```

---

# 9. Common Errors

## No application encryption key has been specified

Cause

- APP_KEY missing

Fix

- Add APP_KEY in Render Environment Variables
- Redeploy

---

## Database file does not exist

Cause

Wrong SQLite path.

Correct:

```env
DB_DATABASE=/var/www/html/database/database.sqlite
```

---

## Mixed Content (CSS/JS blocked)

Cause

Laravel generated HTTP URLs.

Fix

Render:

```env
APP_URL=https://your-app-name.onrender.com
```

AppServiceProvider:

```php
use Illuminate\Support\Facades\URL;

public function boot(): void
{
    if ($this->app->isProduction()) {
        URL::forceScheme('https');
    }
}
```

Then:

```bash
php artisan optimize:clear
```

Redeploy.

---

## Broken UI

Check:

- npm run build completed
- public/build exists
- manifest.json exists
- Browser console has no 404 errors

---

# 10. Notes

- Do **not** commit `.env`.
- Do **not** commit `database.sqlite`.
- Let Docker create the SQLite file.
- Let Render provide all environment variables.
- Use SQLite only for demo/portfolio projects on the free plan, since the filesystem is ephemeral.