#!/usr/bin/env bash

set -e

# Find app dir
APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../" && pwd -L )"

# Go to it
echo "Changing to $APP_DIR"
cd "$APP_DIR"

# Create env if missing
if [ ! -f ".env" ]; then
    echo "Creating .env file"
    cp .env.example .env
    sed --in-place \
        --regexp-extended \
        --expression='s/^DB_DATABASE=(.+?)$/DB_DATABASE=vscode' \
        --expression='s/^DB_USERNAME=(.+?)$/DB_USERNAME=vscode' \
        --expression='s/^DB_PASSWORD=(.+?)$/DB_PASSWORD=vscode' \
        .env
fi

if [ ! -z "$NOVA_USERNAME" -a ! -z "$NOVA_PASSWORD" ]; then
    echo "Assigning Nova credentials"
    composer config --global http-basic.nova.laravel.com "${NOVA_USERNAME}" "${NOVA_PASSWORD}"
fi

echo "Installing dependencies"
composer install

if grep -q -E '^APP_KEY=$' .env; then
    echo "Setting application key"
    php artisan key:generate
fi

echo "Migrating system"
php artisan migrate --seed

echo "Installing NPM dependencies"
npm install

echo "Building front-end (dev)"
npm run dev
