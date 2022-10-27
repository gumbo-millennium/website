#-------------------------------------------------------------------------------------------------------------
# Copyright (c) Microsoft Corporation. All rights reserved.
# Licensed under the MIT License. See https://go.microsoft.com/fwlink/?linkid=2090316 for license information.
#-------------------------------------------------------------------------------------------------------------

FROM php:8.1-fpm

# Avoid warnings by switching to noninteractive
ENV DEBIAN_FRONTEND=noninteractive

# This Dockerfile adds a non-root 'vscode' user with sudo access. However, for Linux,
# this user's GID/UID must match your local user UID/GID to avoid permission issues
# with bind mounts. Update USER_UID / USER_GID if yours is not 1000. See
# https://aka.ms/vscode-remote/containers/non-root-user for details.
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

# Use production PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Increase max post body size
COPY ./fpm/increase-upload-size.ini "$PHP_INI_DIR/conf.d/99-increase-upload-size.ini"

# Configure apt and install packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends apt-utils dialog 2>&1 \
    && apt-get install -y curl git iproute2 procps lsb-release unzip zip openssl gnupg vim zsh \
    && apt-get install -y nginx supervisor \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt

# Configure en_US locale
RUN apt-get update \
    && apt-get install -y locales \
    && echo 'en_US.UTF-8 UTF-8' >> /etc/locale.gen \
    && locale-gen en_US.UTF-8 \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt

# Install MySQL client
COPY ./bin/mysql-apt-config_0.8.22-1_all.deb /tmp/mysql-apt-config_0.8.22-1_all.deb
RUN apt-get update \
    && apt-get install wget \
    && dpkg -i /tmp/mysql-apt-config_0.8.22-1_all.deb \
    && apt-get update \
    && apt-get install -y mysql-client \
    && apt-get clean \
    && rm -rf /tmp/mysql-apt-config_0.8.22-1_all.deb \
    && rm -rf /var/cache/apt /var/lib/apt

# Install xdebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install redis
RUN yes '' | pecl install redis \
    && echo "extension=$(find /usr/local/lib/php/extensions/ -name redis.so)" > /usr/local/etc/php/conf.d/redis.ini

# Install libzip-dev
RUN apt-get update \
    && apt-get install -y libzip-dev libpng-dev libjpeg-dev libsqlite3-dev \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt

# Install zip, bcmath, mysqli, sqlite, pdo and pdo for MySQL and sqlite
RUN docker-php-ext-configure zip \
    && docker-php-ext-install zip gd bcmath \
    && docker-php-ext-install mysqli pdo pdo_mysql pdo_sqlite \
    && docker-php-ext-configure gd \
    && docker-php-ext-install gd pcntl exif \
    && docker-php-source delete

# Install Node LTS
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt

# Install and configure supervisord
RUN apt-get update \
    && apt-get install -y supervisor \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt
RUN groupadd --system supervisor
COPY ./supervisor/*.conf /etc/supervisor/conf.d/

# Install Composer from the Composer docker image, and auto-bind user composer dir on login
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN echo 'export PATH="$PATH:$( composer config --global --absolute bin-dir )"' > /etc/profile.d/composer.sh \
    && chmod 0555 /etc/profile.d/composer.sh

# Create a non-root user to use - see https://aka.ms/vscode-remote/containers/non-root-user.
RUN groupadd --gid $USER_GID $USERNAME \
    && useradd -s /bin/bash --uid $USER_UID --gid $USER_GID -G www-data,supervisor -m $USERNAME \
    && apt-get update \
    && apt-get install -y sudo \
    && apt-get clean \
    && rm -rf /var/cache/apt /var/lib/apt \
    && echo "$USERNAME ALL=(root) NOPASSWD:ALL" > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# Copy PHP-FPM config and self-test
COPY ./fpm/docker-pool.conf $PHP_INI_DIR/../php-fpm.d/zz-docker.conf
RUN php-fpm --test

# Install phpmyadmin
RUN curl -o /tmp/pma.tar.gz -L https://files.phpmyadmin.net/phpMyAdmin/5.2.0/phpMyAdmin-5.2.0-english.tar.xz \
    && mkdir /var/www/phmyadmin \
    && tar -xf /tmp/pma.tar.gz -C /var/www/phmyadmin --strip-components=1 \
    && rm /tmp/pma.tar.gz
COPY ./phpmyadmin/config.inc.php /var/www/phmyadmin/config.inc.php

# Configure nginx and self-test
RUN sed -i "s/worker_processes /daemon off;\nworker_processes /" /etc/nginx/nginx.conf
COPY ./nginx/default.conf /etc/nginx/sites-available/default
RUN nginx -t

# Configure entrypoint
ENTRYPOINT ["supervisord"]

# Alias some commands
RUN echo "alias pa='php /workspace/artisan'" >> /etc/profile.d/codespaces-laravel.sh && \
    echo "alias ls='ls -B -h --color=auto -ltr'" >> /etc/profile.d/codespaces-laravel.sh && \
    echo "alias apt='sudo apt'" >> /etc/profile.d/codespaces-laravel.sh  && \
    echo "alias apt-get='sudo apt-get'" >> /etc/profile.d/codespaces-laravel.sh \
    && chmod 0555 /etc/profile.d/codespaces-laravel.sh

# Override some ENV values
ENV DB_CONNECTION=mysql
ENV DB_HOST=mysql
ENV DB_PORT=3306
ENV DB_DATABASE=vscode
ENV DB_USERNAME=vscode
ENV DB_PASSWORD=vscode

# Install setup-container script somewhere clever
COPY ./bin/setup-container.sh /usr/local/bin/setup-container.sh
RUN chmod 0555 /usr/local/bin/setup-container.sh

# Switch back to dialog for any ad-hoc use of apt-get
ENV DEBIAN_FRONTEND=
