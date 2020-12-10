#!/usr/bin/env sh

sed -i "s/\$PORT/$PORT/" /etc/nginx/conf.d/default.conf

sudo -u app php /var/www/laravel/artisan migrate
sudo -u app php /var/www/laravel/artisan optimize

exec supervisord -c /etc/supervisord.conf
