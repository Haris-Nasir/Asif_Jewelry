# Laravel 8 on PHP 8.4 for Railway (and other Docker hosts)

FROM php:8.4-cli-bookworm AS php-base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring xml zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php-base AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN composer dump-autoload --optimize --no-interaction \
    && php artisan package:discover --ansi

FROM node:20-bookworm AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run production

FROM php:8.4-cli-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip4 \
    libpng16-16 \
    libonig5 \
    libxml2 \
    && docker-php-ext-install pdo_mysql mbstring xml zip gd \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=vendor /app /app
COPY --from=frontend /app/public /app/public

ENV APP_ENV=production

EXPOSE 8080

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
