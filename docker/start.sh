#!/bin/sh
set -e

# Railway injects PORT at runtime — do not hardcode in Dockerfile
PORT="${PORT:-8080}"

echo "Listening on 0.0.0.0:${PORT} (set Railway Networking target port to match)"

mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chmod -R 775 storage bootstrap/cache

php -d error_reporting=22527 artisan migrate --force
php -d error_reporting=22527 artisan config:cache
php -d error_reporting=22527 artisan view:cache

exec php -d error_reporting=22527 artisan serve \
    --host=0.0.0.0 \
    --port="${PORT}" \
    --no-reload
