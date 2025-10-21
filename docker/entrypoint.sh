#!/bin/sh
set -e

# Run as root to ensure we have permissions to create directories and set permissions
if [ "$(id -u)" = '0' ]; then
    # Create necessary directories with proper permissions
    mkdir -p /var/www/html/storage/framework/sessions
    mkdir -p /var/www/html/storage/framework/views
    mkdir -p /var/www/html/storage/framework/cache
    mkdir -p /var/www/html/bootstrap/cache
    mkdir -p /var/www/html/storage/logs

    # Set directory ownership and permissions
    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache

    # Create log file and set permissions
    touch /var/www/html/storage/logs/laravel.log
    chown www-data:www-data /var/www/html/storage/logs/laravel.log
    chmod 664 /var/www/html/storage/logs/laravel.log

    # Switch to www-data user and run the rest of the script
    exec su-exec www-data "$0" "$@"
fi

# The rest of the script runs as www-data
cd /var/www/html

# Generate application key if not exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
fi

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan db:monitor > /dev/null 2>&1; do
    echo "Waiting for database connection..."
    sleep 1
done

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and cache routes and config
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:cache
php artisan view:cache

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm
