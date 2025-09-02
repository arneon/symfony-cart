FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    g++ \
    make \
    curl \
    libsqlite3-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl pdo pdo_mysql mbstring exif pcntl bcmath gd zip xml opcache pdo_sqlite

RUN pecl install redis && docker-php-ext-enable redis

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY 99-xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
