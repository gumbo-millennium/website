# vim: set ft=dockerfile :
# Configuration file for Docker

# Source from Roelof's PHP image
FROM roelofr/php:7.4-apache AS laravel

# Install Redis as native extension
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Use production PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Change mail and file limits
COPY ./php/*.ini $PHP_INI_DIR/conf.d/

# Add Laravel group and user
RUN groupadd \
        --gid 1000 \
        laravel \
    && useradd \
        --home-dir /var/www \
        --comment "Laravel User" \
        --no-create-home \
        --uid 1000 \
        --gid 1000 \
        --groups www-data \
        --password "" \
        laravel

# Change root to public
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Add apache config
ENV APACHE_RUN_USER=#1000 APACHE_RUN_GROUP=#1000
COPY ./apache/conf/*.conf /etc/apache2/conf-available/
COPY ./apache/mods/*.conf /etc/apache2/mods-available/
RUN a2enmod \
        authz_core \
        brotli \
        expires \
        headers \
        proxy_http \
        rewrite \
    && a2enconf \
        mailhog \
        phpmyadmin

# Configure Laravel branch
FROM laravel AS horizon

# Run as Laravel
USER laravel

# Make sure we're working from /var/www
WORKDIR /var/www

# Run horizon when this container starts
CMD [ "php", "/var/www/artisan", "horizon" ]
