# ==============================================================================
# Multi-stage Dockerfile for SIPPOL Backend (Laravel 12 API)
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

# Stage 2: Production PHP-FPM + Nginx runtime
FROM php:8.2-fpm-alpine AS runner
WORKDIR /var/www/html

# Install mlocati fast extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install system dependencies & PHP extensions efficiently
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    sqlite \
    && install-php-extensions \
        pdo_mysql \
        pdo_sqlite \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        zip \
    && mkdir -p /var/log/supervisor \
    && rm -rf /tmp/* /var/cache/apk/*

# Copy Nginx, PHP, and Supervisor configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini $PHP_INI_DIR/conf.d/99-custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application source & vendor
COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor

# Set storage and bootstrap cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://127.0.0.1:8000/up || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
