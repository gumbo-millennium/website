# vim: set ft=dockerfile :
# Configuration file for Docker

FROM wordpress:latest

RUN curl -o /usr/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/bin/wp \
    && mkdir /var/www/.wp-cli \
    && chown www-data /var/www/.wp-cli

COPY wordpress/php.ini /usr/local/etc/php/conf.d/upload-size.ini
