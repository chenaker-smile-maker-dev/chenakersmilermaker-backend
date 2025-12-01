#!/bin/bash
set -e

echo "=== Running Database Seeders ==="
php artisan db:seed --force
echo "✓ Database seeded!"
