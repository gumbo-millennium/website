# vim: set ft=dockerfile :
# Configuration file for Docker web

# Based on the lightweight alpine image
FROM nginx

# Overwrite default config
COPY nginx/vhost.conf /etc/nginx/conf.d/default.conf

# Use /var/www as working directory.
WORKDIR /var/www
