#!/bin/sh
set -e
cd /app

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
if [ -n "$KOYEB_PUBLIC_DOMAIN" ]; then
    export APP_URL="https://${KOYEB_PUBLIC_DOMAIN}"
elif [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi
export APP_URL="${APP_URL:-https://localhost}"

if [ -n "$DATABASE_URL" ]; then
    export DB_URL="$DATABASE_URL"
    export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
else
    export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
    export DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

mkdir -p storage/app/papers storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
