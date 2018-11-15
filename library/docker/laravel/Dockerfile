# vim: set ft=dockerfile :
# Configuration file for Docker

FROM roelofr/php:fpm

# Raise PHP limit
COPY laravel/php.ini /usr/local/etc/php/conf.d/upload-size.ini

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

# Install two programs
# - supervisor, which runs the supervisor daemon running BOTH php-fpm (PHP host)
#   and Horizon (queue worker)
# - Imagick, which is used to convert PDF files to JPG / PNG files. (it has the
#   godly 'convert' command, which converts just about anything)
RUN apt-get update \
  && apt-get install -yq \
    ghostscript \
    imagemagick \
    supervisor \
    exiftool \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# Add Laravel and Horizon configurations
COPY \
    laravel/php-fpm.conf \
    laravel/horizon.conf \
    /etc/supervisor/conf.d/

WORKDIR /var/www

# Override the php-fpm process to run supervisord instead
CMD ["supervisord", "--nodaemon", "--directory=/var/www", "--user=root", "--configuration=/etc/supervisor/supervisord.conf"]
