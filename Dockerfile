# syntax=docker/dockerfile:1
# Laravel 8 — Railway production image (PHP 8.4+, Docker-only; no Railpack/Nixpacks)

FROM php:8.4.3-cli-bookworm AS php-base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip gd opcache \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- PHP dependencies (must run on PHP 8.4+) ---
FROM php-base AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN php -v \
    && composer --version \
    && composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts \
    && composer check-platform-reqs

COPY . .

RUN composer dump-autoload --optimize --no-interaction \
    && php artisan package:discover --ansi

# --- Frontend assets (Laravel Mix) ---
FROM node:20-bookworm AS frontend

WORKDIR /app

ENV NODE_OPTIONS=--openssl-legacy-provider

COPY package.json package-lock.json ./
RUN npm ci

COPY webpack.mix.js ./
COPY resources ./resources
COPY public ./public

RUN npm run production

# --- Production runtime ---
FROM php-base AS runtime

WORKDIR /app

COPY --from=vendor /app /app
COPY --from=frontend /app/public/js /app/public/js
COPY --from=frontend /app/public/css /app/public/css
COPY --from=frontend /app/public/mix-manifest.json /app/public/mix-manifest.json

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

ENV APP_ENV=production \
    APP_DEBUG=false

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
