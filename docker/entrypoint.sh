#!/bin/bash

cd /var/www/html

echo "Starting application..."

# Ensure vendor folder exists and install dependencies if missing
if [ ! -d "vendor" ]; then
    echo "🔧 Installing composer dependencies..."
    composer install --no-dev --no-interaction --optimize-autoloader 2>&1 || {
        echo "⚠️  Composer install failed, attempting basic install..."
        composer install --no-dev --no-interaction 2>&1 || echo "⚠️  Composer install skipped"
    }
    composer dump-autoload --optimize 2>&1 || true
else
    echo "✓ Vendor folder exists"
fi

# Run migrations on first boot (only for app service, not horizon/scheduler)
if [ ! -f ".migrated" ] && [ -f "artisan" ]; then
    echo "🗄️  Running migrations..."
    php artisan migrate --force 2>&1 || echo "⚠️  Migrations failed or already run"

    echo "🌱 Seeding database..."
    php artisan db:seed --force 2>&1 || echo "⚠️  Seeding skipped"

    touch .migrated
    echo "✓ Database initialized"
fi

echo "✓ Application ready"

# Start PHP-FPM
exec php-fpm
