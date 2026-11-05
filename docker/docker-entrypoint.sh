#!/bin/sh
set -e

# 0. Clean stale bootstrap caches (e.g. dev service providers from build host)
rm -f /app/bootstrap/cache/*.php
mkdir -p /app/bootstrap/cache

# 1. Ensure .env exists so artisan key:generate and configs work properly
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
chmod 666 /app/.env 2>/dev/null || true

# Extract and export clean APP_KEY from /app/.env (stripping quotes and CRLF)
if [ -f /app/.env ]; then
    CLEAN_KEY=$(grep -E "^APP_KEY=" /app/.env | head -n 1 | cut -d '=' -f2- | tr -d '\r' | tr -d '"' | tr -d "'")
    if [ -n "$CLEAN_KEY" ]; then
        export APP_KEY="$CLEAN_KEY"
    fi
fi

# 2. Handle Database
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Checking MySQL connection at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."
    MAX_TRIES=30
    COUNT=0
    until php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $db   = getenv("DB_DATABASE") ?: "sippol";
        $user = getenv("DB_USERNAME") ?: "root";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
                PDO::ATTR_TIMEOUT => 2,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            exit(0);
        } catch (\Exception $e) {
            exit(1);
        }
    ' 2>/dev/null; do
        COUNT=$((COUNT + 1))
        if [ "$COUNT" -gt "$MAX_TRIES" ]; then
            echo "⚠️  Timeout waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}."
            break
        fi
        echo "Waiting for MySQL to become ready... (attempt $COUNT/$MAX_TRIES)"
        sleep 2
    done
    echo "✅ MySQL connection successfully established!"
elif [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]; then
    DB_PATH="${DB_DATABASE:-/app/database/database.sqlite}"
    if [ ! -f "$DB_PATH" ]; then
        echo "Creating SQLite database file at $DB_PATH..."
        mkdir -p "$(dirname "$DB_PATH")"
        touch "$DB_PATH"
        chown -R www-data:www-data "$(dirname "$DB_PATH")"
        chmod 664 "$DB_PATH"
    fi
fi

# 3. Generate APP_KEY if missing
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generating application key..."
    php artisan key:generate --force
    CLEAN_KEY=$(grep -E "^APP_KEY=" /app/.env | head -n 1 | cut -d '=' -f2- | tr -d '\r' | tr -d '"' | tr -d "'")
    if [ -n "$CLEAN_KEY" ]; then
        export APP_KEY="$CLEAN_KEY"
    fi
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
php artisan storage:link --no-interaction 2>/dev/null || true

# 5. Database migrations (manual by default for production safety)
if [ "${AUTORUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --no-interaction

    # Run seeders if RUN_SEEDERS=true
    if [ "${RUN_SEEDERS:-false}" = "true" ]; then
        echo "Running database seeders..."
        php artisan db:seed --force --no-interaction || true
    fi
else
    echo "ℹ️  Automatic database migration is disabled (AUTORUN_MIGRATIONS=false)."
    echo "   To run migrations manually: docker compose exec backend php artisan migrate --force"
fi

# 6. Ensure stale config/route/view caches are cleared on start
php artisan config:clear --no-interaction 2>/dev/null || true
php artisan route:clear --no-interaction 2>/dev/null || true
php artisan view:clear --no-interaction 2>/dev/null || true

# Execute CMD
exec "$@"
