FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update
RUN apt-get install -y \
    zip unzip curl \
    && docker-php-ext-install pdo_mysql

ARG USER_ID=1000
ARG GROUP_ID=1000
ARG USER_NAME=web_user

RUN groupadd -g ${GROUP_ID} ${USER_NAME} \
    && useradd -u ${USER_ID} -g ${USER_NAME} -m ${USER_NAME}

USER ${USER_NAME}

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
