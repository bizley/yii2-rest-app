FROM php:7.3-fpm-alpine
RUN apk update \
    && apk add --no-cache \
        $PHPIZE_DEPS \
        ca-certificates \
        icu-dev \
        libzip-dev \
        libxml2-dev \
    && pecl install xdebug-2.9.0 \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install calendar \
    && docker-php-ext-install zip \
    && docker-php-ext-install intl \
    && docker-php-ext-install xml \
    && docker-php-ext-enable xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer; \
echo $'xdebug.remote_port=9000 \n\
xdebug.remote_enable=1 \n\
xdebug.remote_connect_back=1' >> /usr/local/etc/php/conf.d/xdebug.ini;
