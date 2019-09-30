# vim: set ft=dockerfile :
# Configuration file for Docker

# Source from Roelof's PHP image
FROM roelofr/php:7.3-fpm AS laravel

# Install Redis as native extension
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear

# Raise PHP limit
COPY php.ini /usr/local/etc/php/conf.d/laravel.ini

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

# Make it really certain we're using the right user
RUN { \
    echo '[www]'; \
    echo 'user = laravel'; \
    echo 'group = laravel'; \
    } | tee /usr/local/etc/php-fpm.d/zz-laravel.conf

# Configure Laravel branch
FROM laravel AS horizon

# Run as Laravel
USER laravel

# Make sure we're working from /var/www
WORKDIR /var/www

# Run horizon when this container starts
CMD [ "php", "/var/www/artisan", "horizon" ]
