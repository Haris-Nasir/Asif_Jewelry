#!/bin/sh
set -e

PORT="${PORT:-8080}"

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
