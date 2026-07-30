#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force
fi

exec "$@"
