#!/bin/bash
set -e

echo "=== Initializing Database ==="
echo "Waiting for MySQL to be ready..."
sleep 10

echo "Running migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "✓ Database initialization complete!"
