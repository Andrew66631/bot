FROM php:8.3-fpm-alpine
RUN apk add --no-cache bash

RUN apk update && apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    curl \
    git \
    supervisor \
    autoconf \
    build-base \
    linux-headers


RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    mbstring \
    xml \
    bcmath

RUN docker-php-source extract && \
    cd /usr/src/php/ext/sockets && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable sockets && \
    docker-php-source delete


RUN apk del autoconf build-base linux-headers


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html


COPY src/ ./


RUN mkdir -p storage storage/framework storage/framework/sessions \
    storage/framework/views storage/framework/cache storage/logs bootstrap/cache


RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]