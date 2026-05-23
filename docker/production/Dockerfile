# syntax=docker/dockerfile:1

ARG NODE_VERSION=24
ARG PHP_VERSION=8.5
ARG COMPOSER_VERSION=2
ARG BUN_VERSION=1.3.14

FROM node:${NODE_VERSION}-bookworm AS assets

WORKDIR /var/www/html

ARG BUN_VERSION=1.3.14

RUN npm install -g bun@"${BUN_VERSION}"

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

COPY components.json tsconfig.json vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN bun run build

FROM composer:${COMPOSER_VERSION} AS composer_image

FROM php:${PHP_VERSION}-fpm-bookworm AS base

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        default-mysql-client \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        unzip \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mysqli \
        pcntl \
        pdo \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer_image /usr/bin/composer /usr/bin/composer
COPY docker/production/php.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY docker/production/nginx.conf /etc/nginx/nginx.conf

WORKDIR /var/www/html

FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM base AS runtime

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /var/www/html/public/build ./public/build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && php artisan package:discover --ansi

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD php artisan about --only=environment >/dev/null || exit 1

CMD ["php-fpm"]
