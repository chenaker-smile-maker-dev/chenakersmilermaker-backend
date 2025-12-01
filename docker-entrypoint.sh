#!/bin/bash

set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! nc -z mysql 3306 2>/dev/null; do
  sleep 1
done
echo "MySQL is ready!"

# Run Laravel setup
echo "Running Laravel setup..."
php artisan config:cache
php artisan migrate --force

# Start PHP-FPM
exec php-fpm
