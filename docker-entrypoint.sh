#!/bin/bash
set -e

# Create .env file from environment variables if it doesn't exist
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file from environment variables..."
    cat > /var/www/html/.env << 'ENVEOF'
APP_NAME=${APP_NAME:-Laravel}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-true}
APP_URL=${APP_URL:-http://localhost}

DB_CONNECTION=${DB_CONNECTION:-sqlite}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE:-database/database.sqlite}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

CACHE_STORE=${CACHE_STORE:-file}
SESSION_DRIVER=${SESSION_DRIVER:-file}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-debug}

DECART_API_KEY=${DECART_API_KEY}
DECART_MODEL=${DECART_MODEL:-lucy-vton-3.6}

FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
ENVEOF
    chown www-data:www-data /var/www/html/.env
    chmod 644 /var/www/html/.env
fi

# Create storage directories with correct permissions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/cache
chown -R www-data:www-data /var/www/html/storage
chmod -R 777 /var/www/html/storage

# Ensure laravel.log exists and is writable
touch /var/www/html/storage/logs/laravel.log
chown www-data:www-data /var/www/html/storage/logs/laravel.log
chmod 666 /var/www/html/storage/logs/laravel.log

echo "Running database migrations..."
php artisan migrate --force

echo "Clearing caches..."
php artisan config:cache || true
php artisan view:clear || true

echo "Starting Apache..."

# Monitor logs in background
tail -f /var/www/html/storage/logs/laravel.log 2>/dev/null &
tail -f /var/www/html/storage/logs/php-errors.log 2>/dev/null &

exec apache2-foreground
