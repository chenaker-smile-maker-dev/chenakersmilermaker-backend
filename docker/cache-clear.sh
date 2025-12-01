#!/bin/bash
set -e

echo "=== Clearing Application Cache ==="
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "✓ Cache cleared!"
