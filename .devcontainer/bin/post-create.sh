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
    sed --regexp-extended \
        --expression='s/^DB_USERNAME=(.+?)$/DB_USERNAME=vscode/' \
        --expression='s/^DB_PASSWORD=(.+?)$/DB_PASSWORD=vscode/' \
        --expression='s/^APP_KEY=$/APP_KEY=base64:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa=/' \
        .env.example \
        > .env
fi

if [ ! -z "$NOVA_USERNAME" -a ! -z "$NOVA_PASSWORD" ]; then
    echo "Assigning Nova credentials"
    composer config --global http-basic.nova.laravel.com "${NOVA_USERNAME}" "${NOVA_PASSWORD}"
fi

echo "Installing dependencies"
composer install --no-interaction

if grep -q -E '^APP_KEY=(base64:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa=)?$' .env; then
    echo "Setting application key"
    php artisan key:generate
fi

echo "Installing NPM dependencies"
npm install

echo "Building front-end (dev)"
npm run dev
