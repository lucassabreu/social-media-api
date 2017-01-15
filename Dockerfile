FROM php:7

RUN apt-get update && \
    apt-get install -y wget zip git libfreetype6-dev \
        libmcrypt-dev libssl-dev libicu-dev libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo pdo_sqlite pdo_mysql intl
