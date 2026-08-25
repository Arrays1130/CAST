#!/bin/sh
set -e
cd /app

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
export SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
export SESSION_ENCRYPT="${SESSION_ENCRYPT:-true}"

if [ -n "$KOYEB_PUBLIC_DOMAIN" ]; then
    export APP_URL="https://${KOYEB_PUBLIC_DOMAIN}"
elif [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi
export APP_URL="${APP_URL:-https://localhost}"

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Neon / Render inject DATABASE_URL. .env.example is MySQL — overwrite those keys.
if [ -n "$DATABASE_URL" ]; then
    export DB_URL="$DATABASE_URL"
    export DB_CONNECTION=pgsql
    export DB_PORT=5432
    export DB_SSLMODE="${DB_SSLMODE:-require}"
    export SESSION_DRIVER=database
    export CACHE_STORE=database
    php -r '
        $path = ".env";
        $env = file_exists($path) ? file_get_contents($path) : "";
        foreach (["DB_CONNECTION", "DB_HOST", "DB_PORT", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD", "DB_URL", "DB_SSLMODE"] as $key) {
            $env = preg_replace("/^".$key."=.*$/m", "", $env);
        }
        $env = trim($env)."\n\nDB_CONNECTION=pgsql\nDB_PORT=5432\nDB_URL=".getenv("DATABASE_URL")."\nDB_SSLMODE=require\n";
        file_put_contents($path, $env);
    '
elif [ "$APP_ENV" = "production" ]; then
    echo "DATABASE_URL is required in production. Create a free Neon Postgres database and set DATABASE_URL on Render." >&2
    exit 1
else
    export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
    export DB_DATABASE="${DB_DATABASE:-/app/database/database.sqlite}"
    export SESSION_DRIVER="${SESSION_DRIVER:-file}"
    export CACHE_STORE="${CACHE_STORE:-file}"
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
fi

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is required in production. Set it in the Render dashboard (php artisan key:generate --show)." >&2
    if [ "$APP_ENV" = "production" ]; then
        exit 1
    fi
    php artisan key:generate --force
fi

mkdir -p storage/app/papers storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache || true

# HTTP API mail is more reliable on Render Free than Gmail SMTP (often delayed/blocked).
if [ -n "$RESEND_API_KEY" ]; then
    export MAIL_MAILER=resend
fi

php artisan config:clear
php artisan migrate --force

# Never seed known demo passwords on production unless SEED_DEMO=true
if [ "${SEED_DEMO}" = "true" ] || [ "${APP_ENV}" = "local" ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
