#!/bin/bash

cd /var/www/html

echo "Starting Scheduler..."

# Ensure vendor folder exists and install dependencies if missing
if [ ! -d "vendor" ]; then
    echo "🔧 Installing composer dependencies..."
    composer install --no-dev --no-interaction --optimize-autoloader 2>&1 || {
        echo "⚠️  Composer install failed, attempting basic install..."
        composer install --no-dev --no-interaction 2>&1 || echo "⚠️  Composer install skipped"
    }
    composer dump-autoload --optimize 2>&1 || true
fi

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
sleep 5

echo "✓ Starting Scheduler"

# Run scheduler in foreground
exec php artisan schedule:work
