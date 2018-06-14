# vim: set ft=dockerfile :
# Configuration file for Docker

FROM roelofr/php:fpm

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

# Make it really certain
RUN { \
	echo '[www]'; \
	echo 'user = laravel'; \
	echo 'group = laravel'; \
    } | tee /usr/local/etc/php-fpm.d/zz-laravel.conf

# Install supervisord
RUN apt-get update \
  && apt-get install -y supervisor \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Add Laravel and Horizon configurations
COPY \
    ./library/docker/laravel/php-fpm.conf \
    ./library/docker/laravel/horizon.conf \
    /etc/supervisor/conf.d/

WORKDIR /var/www

# Override the php-fpm process to run supervisord instead
CMD ["supervisord", "--nodaemon", "--directory=/var/www", "--user=root", "--configuration=/etc/supervisor/supervisord.conf"]
