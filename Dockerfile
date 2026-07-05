FROM php:8.2-cli

# System packages + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd

# Install Node.js 16
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy project
COPY . .

# Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

# Fix for older webpack/mix builds
ENV NODE_OPTIONS=--openssl-legacy-provider

# NPM
RUN npm install --legacy-peer-deps
RUN npm run production

# Laravel permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENV PORT=8080

CMD php artisan serve --host=0.0.0.0 --port=$PORT
