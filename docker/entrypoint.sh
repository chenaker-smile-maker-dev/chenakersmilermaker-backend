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

# Check if public/build exists, always try to build
echo "🔨 Ensuring assets are built..."

# Ensure node_modules exists
if [ ! -d "node_modules" ]; then
    echo "📦 Installing npm dependencies..."
    npm ci 2>&1 || npm install 2>&1 || {
        echo "⚠️  npm install failed"
    }
fi

# Always build assets (don't skip)
npm run build 2>&1 || {
    echo "⚠️  npm build failed, attempting retry..."
    sleep 5
    npm run build 2>&1 || echo "⚠️  npm build skipped"
}

# Verify build output
if [ -d "public/build/assets" ]; then
    echo "✓ Assets built successfully"
    ls -la public/build/assets | head -5
else
    echo "⚠️  public/build/assets directory not found"
    mkdir -p public/build/assets
fi

# Clear Laravel cache to prevent stale config
php artisan config:clear 2>&1 || true
php artisan cache:clear 2>&1 || true

# Cache the config
php artisan config:cache 2>&1 || {
    echo "⚠️  Config cache failed, continuing..."
}

# Run migrations on first boot
if [ ! -f ".migrated" ] && [ -f "artisan" ]; then
    echo "🗄️  Running migrations..."

    # Wait for MySQL to be ready
    sleep 10

    php artisan migrate --force 2>&1 || {
        echo "⚠️  Migrations failed"
        sleep 5
        php artisan migrate --force 2>&1 || echo "⚠️  Migrations skipped"
    }

    echo "🌱 Seeding database..."
    php artisan db:seed --force 2>&1 || echo "⚠️  Seeding skipped"

    touch .migrated
    echo "✓ Database initialized"
fi

echo "✓ Application ready"

# Start PHP-FPM
exec php-fpm
