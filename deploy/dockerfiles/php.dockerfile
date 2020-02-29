FROM php:7.4-fpm

RUN apt-get update

# Install dependencies
RUN apt-get install -y libpq-dev zlib1g-dev libzip-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip
