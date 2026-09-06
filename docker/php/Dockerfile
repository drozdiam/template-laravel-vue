FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        postgresql-dev \
        oniguruma-dev \
        libzip-dev \
        autoconf \
        g++ \
        make \
        zip \
        unzip \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        mbstring \
        zip \
        pcntl \
        bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del autoconf g++ make

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/app
