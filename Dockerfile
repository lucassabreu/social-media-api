FROM php:7

RUN apt-get update && \
    apt-get install -y zip git
RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql
