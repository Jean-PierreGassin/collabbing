ARG PHP_VERSION=8.5
ARG PHP_VARIANT=fpm-bookworm
ARG APP_CONTAINER_PATH=/var/www/html
ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} AS composer_image

FROM php:${PHP_VERSION}-${PHP_VARIANT}

ARG APP_CONTAINER_PATH=/var/www/html
ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        bash-completion \
        zsh \
        git \
        unzip \
        zip \
        curl \
        gettext-base \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        default-mysql-client \
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
    && pecl install redis xdebug \
    && docker-php-ext-enable redis xdebug \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN printf '%s\n' \
    'autoload -Uz compinit' \
    'compinit' \
    > /etc/zsh/zshrc

COPY --from=composer_image /usr/bin/composer /usr/bin/composer
COPY php/php.ini.template /usr/local/etc/php/php.ini.template
COPY php/xdebug.ini.template /usr/local/etc/php/xdebug.ini.template
COPY php/entrypoint.sh /usr/local/bin/app-php-entrypoint

WORKDIR ${APP_CONTAINER_PATH}
ENTRYPOINT ["app-php-entrypoint"]
CMD ["php-fpm"]
