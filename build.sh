#!/usr/bin/env bash
# Exit on error
set -e

echo "=== Building Personality 360 Assessment for Render ==="

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets
npm install
npm run build

# Cache configurations and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Build succeeded! ==="
