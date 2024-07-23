#!/usr/bin/env bash

set -e

# Find app dir
APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )/../../" && pwd -L )"

# Go to it
echo "Changing to $APP_DIR"
cd "$APP_DIR"

# Seed database
echo "Migrating system"
php artisan migrate --seed
