# vim: set ft=dockerfile :
# Configuration file for Docker

# Configuration file for Docker
FROM php:7.4-alpine AS laravel

# Add nginx
RUN apk update \
    && apk add nginx \
    && adduser -D -g www www \
    && mkdir /www \
    && chown -R www:www /var/lib/nginx \
    && chown -R www:www /www \
    && sed -i 's/80/${PORT}/' /etc/nginx/nginx.conf \
    && echo '' > /etc/nginx/docker-aliases.conf
COPY ./nginx-default.conf /etc/nginx/conf.d/default.conf

# Install PHP extensions
RUN apk update \
    && apk add curl oniguruma libzip \
    && apk add --virtual php-deps curl-dev oniguruma-dev libzip-dev \
    && docker-php-ext-install \
        bcmath \
        curl \
        exif \
        json \
        mbstring \
        mysqli \
        pcntl \
        pdo \
        pdo_mysql \
        zip \
    && docker-php-source delete \
    && apk del php-deps

# Install PECL extensions
RUN apk add --no-cache --virtual php-deps ${PHPIZE_DEPS} \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear \
    && docker-php-source delete \
    && apk del php-deps

# Install Imagick
RUN apk add --no-cache imagemagick6 \
    && apk add --no-cache --virtual php-deps imagemagick6-dev ${PHPIZE_DEPS} \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /tmp/pear \
    && docker-php-source delete \
    && apk del php-deps


# Use production PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Add cloud user
RUN addgroup --gid 1010 gumbo \
    && adduser \
        -h /var/www \
        -H \
        -u 1010 \
        -G gumbo \
        -D \
        gumbo \
    && adduser gumbo www-data \
    && adduser gumbo www

# Change tempdir
RUN mkdir -p /usr/local/tmp/gumbo && \
        chown gumbo:gumbo /usr/local/tmp/gumbo
ENV TMPDIR=/usr/local/tmp/gumbo

# Configure PHP-FPM
COPY ./fpm-pool.conf $PHP_INI_DIR/../php-fpm.d/www.conf

# Configure Supervisord
COPY ./supervisor/*.ini /etc/supervisor.d/

# Start with supervisor
COPY ./start-supervisor.sh /usr/sbin/start-supervisor
CMD ['/usr/sbin/start-supervisor']

# Build a package that we can run locally
FROM laravel AS laravel-dev

# Configure aliases
COPY ./nginx-aliases.conf /etc/nginx/docker-aliases.conf

# Install Composer
RUN curl -Lo /tmp/composer-setup.php https://github.com/composer/getcomposer.org/raw/e3e43bde99447de1c13da5d1027545be81736b27/web/installer \
    && php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer \
    && rm /tmp/composer-setup.php

# Build a package that can run Horizon
FROM laravel-dev AS horizon-dev

# Start Horizon on process start
CMD ['php', 'artisan', 'horizon']

# And build one for our servers
FROM laravel AS laravel-bundle

# Install code
WORKDIR /var/www/laravel
COPY --chown=gumbo:www . ./

# Prep env
RUN chmod 0755 /var/www/laravel
COPY .env.example .env
