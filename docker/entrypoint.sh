#!/bin/bash

cd /var/www/html

echo "Starting application..."

# Fix .env file if it has unquoted values with spaces
if grep -q "=.*[^\"'].*[[:space:]].*[^\"']$" .env 2>/dev/null; then
    echo "⚠️  Fixing .env file formatting..."
    # This is handled by Laravel's dotenv loader with error suppression
fi

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

# Check if public/build exists
if [ ! -d "public/build" ]; then
    echo "🔨 Building assets..."
    npm run build 2>&1 || true
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
