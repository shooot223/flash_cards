FROM composer:2 AS composer_builder
WORKDIR /app

COPY web/laravel/composer.json web/laravel/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

COPY web/laravel/ ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

FROM node:22 AS node_builder
WORKDIR /app

COPY web/laravel/package*.json ./
RUN npm ci

COPY web/laravel/ ./
RUN npm run build

FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer_builder /app /var/www/html
COPY --from=node_builder /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]
