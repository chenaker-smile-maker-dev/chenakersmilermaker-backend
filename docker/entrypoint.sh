#!/bin/bash
set -e

# Ensure vendor folder exists
if [ ! -d "/var/www/html/vendor" ]; then
    echo "Installing composer dependencies..."
    cd /var/www/html
    composer install --no-dev --no-interaction --optimize-autoloader || composer install --no-dev --no-interaction
fi

# Run migrations on first boot (check if migrations table exists)
if [ ! -f "/var/www/html/.migrated" ]; then
    echo "Running migrations..."
    php /var/www/html/artisan migrate --force || true
    php /var/www/html/artisan db:seed --force || true
    touch /var/www/html/.migrated
fi

# Start PHP-FPM
exec php-fpm
