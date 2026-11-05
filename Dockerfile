# ==============================================================================
# Multi-stage Dockerfile for SIPPOL Backend (Laravel 12 API with FrankenPHP)
# ==============================================================================

# Stage 1: Build composer dependencies
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# Stage 2: Production FrankenPHP runtime
FROM dunglas/frankenphp:1-php8.2-alpine AS runner
WORKDIR /app

# Install system libraries & PHP extensions
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        bcmath \
        exif \
        pcntl \
        zip \
    && rm -rf /tmp/* /var/cache/apk/*

# Copy FrankenPHP Caddyfile, php.ini, and entrypoint
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application source & vendor
COPY . /app
COPY --from=vendor /app/vendor /app/vendor

# Set storage and bootstrap cache permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

ENV SERVER_NAME=":8000"
EXPOSE 8000

HEALTHCHECK --interval=20s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://127.0.0.1:8000/up || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
