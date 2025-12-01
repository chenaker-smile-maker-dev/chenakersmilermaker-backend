#!/bin/bash
set -e

cd /var/www/html

echo "Starting application..."

# Ensure vendor folder exists and install dependencies
if [ ! -d "vendor" ]; then
    echo "🔧 Installing composer dependencies..."
    composer install --no-dev --no-interaction --optimize-autoloader 2>&1 || {
        echo "⚠️  Composer install failed, attempting basic install..."
        composer install --no-dev --no-interaction 2>&1 || echo "⚠️  Composer install skipped"
    }
else
    echo "✓ Vendor folder exists"
fi

# Generate optimized autoloader
composer dump-autoload --optimize 2>&1 || true

# Run migrations on first boot (check if migrations table exists)
if [ ! -f ".migrated" ]; then
    echo "🗄️  Running migrations..."
    php artisan migrate --force 2>&1 || true

    echo "🌱 Seeding database..."
    php artisan db:seed --force 2>&1 || true

    touch .migrated
    echo "✓ Database initialized"
fi

echo "✓ Application ready"

# Start PHP-FPM
exec php-fpm
