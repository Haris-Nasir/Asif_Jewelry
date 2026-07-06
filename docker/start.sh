#!/bin/sh
set -e

PORT="${PORT:-8000}"

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
