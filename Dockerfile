FROM php:7

RUN apt-get update && \
    apt-get install -y wget zip git libfreetype6-dev \
        libmcrypt-dev libssl-dev libicu-dev libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo pdo_sqlite pdo_mysql intl

RUN cd /tmp && wget http://xdebug.org/files/xdebug-2.5.0.tgz && tar -xvzf xdebug-2.5.0.tgz \
    && cd xdebug-2.5.0 && phpize && ./configure && make && make install \
    && cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20160303 \
    && echo "zend_extension = /usr/local/lib/php/extensions/no-debug-non-zts-20160303/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
&& echo "xdebug.var_display_max_depth=15" >> /usr/local/etc/php/conf.d/xdebug.ini

COPY ./devops/docker/custom.ini /usr/local/etc/php/conf.d/666-custom.ini
