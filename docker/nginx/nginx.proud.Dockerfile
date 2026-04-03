FROM composer:2 AS composer_builder
WORKDIR /app
COPY web/laravel/composer.json web/laravel/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts
COPY web/laravel/ ./

FROM node:22 AS node_builder
WORKDIR /app
COPY web/laravel/package*.json ./
RUN npm ci
COPY web/laravel/ ./
RUN npm run build

FROM nginx:alpine
COPY docker/nginx/default.prod.conf /etc/nginx/conf.d/default.conf
COPY --from=composer_builder /app/public /var/www/html/public
COPY --from=node_builder /app/public/build /var/www/html/public/build
