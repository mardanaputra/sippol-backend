#!/bin/sh
set -e

# 0. Ensure .env exists so artisan key:generate and configs work properly
if [ ! -f /app/.env ]; then
    if [ -f /app/.env.example ]; then
        echo "Creating .env from .env.example..."
        cp /app/.env.example /app/.env
    else
        touch /app/.env
    fi
fi
if ! grep -q "^APP_KEY=" /app/.env 2>/dev/null; then
    echo "APP_KEY=" >> /app/.env
fi
chmod 640 /app/.env
chown root:www-data /app/.env

# 1. Handle SQLite Database if configured
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    DB_PATH="${DB_DATABASE:-/app/database/database.sqlite}"
    if [ ! -f "$DB_PATH" ]; then
        echo "Creating SQLite database file at $DB_PATH..."
        mkdir -p "$(dirname "$DB_PATH")"
        touch "$DB_PATH"
        chown -R www-data:www-data "$(dirname "$DB_PATH")"
        chmod 664 "$DB_PATH"
    fi
fi

# 2. Generate APP_KEY if missing
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generating application key..."
    php artisan key:generate --force
fi

# 3. Fix storage and bootstrap cache permissions
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

# 4. Storage link
php artisan storage:link --no-interaction || true

# 5. Database migrations
if [ "${AUTORUN_MIGRATIONS:-true}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction

    # Run seeders if RUN_SEEDERS=true
    if [ "${RUN_SEEDERS:-false}" = "true" ]; then
        echo "Running database seeders..."
        php artisan db:seed --force --no-interaction
    fi
fi

# 6. Optimize for production if APP_ENV=production
if [ "$APP_ENV" = "production" ]; then
    echo "Optimizing application for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Execute CMD
exec "$@"
