#!/bin/sh
set -e
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
if [ ! -f .env ]; then cp .env.example .env; fi
# Persist one app key across restarts without shipping it in source.
if [ ! -f storage/app-key ]; then php -r 'echo "base64:".base64_encode(random_bytes(32));' > storage/app-key; fi
export APP_KEY="$(cat storage/app-key)"
export APP_URL=http://localhost:8080
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
exec php artisan serve --host=0.0.0.0 --port=8000
