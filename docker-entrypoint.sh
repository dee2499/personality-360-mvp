#!/bin/bash
set -e

# Port configuration (Render passes PORT as an environment variable, defaults to 10000)
PORT="${PORT:-10000}"
sed -ri -e "s/Listen [0-9]+/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri -e "s/<VirtualHost \*:[0-9]+>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf
sed -ri -e "s/:80/:${PORT}/g" /etc/apache2/sites-available/*.conf

# Ensure storage subdirectories and database directory exist
mkdir -p /var/www/html/database
mkdir -p /var/www/html/storage/framework/{sessions,views,cache,testing}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Clear any cached bootstrap files
rm -f /var/www/html/bootstrap/cache/*.php

# Initial permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite

# Run database migrations
php artisan migrate --force

# Seed database if Falcon Group does not exist or if database has no users
FALCON_EXISTS=$(php artisan tinker --execute 'echo \App\Models\Company::where("name", "like", "%Falcon%")->exists() ? "1" : "0";' 2>/dev/null || echo "0")
if [ "$FALCON_EXISTS" != "1" ]; then
    echo "Falcon Group not found. Seeding Falcon Group and 360 assessment data..."
    php artisan db:seed --force
fi

# Ensure default score categories are seeded
php artisan db:seed --class=ScoreCategorySeeder --force

# Re-apply ownership after migrate and seed to ensure www-data can write to SQLite and locks
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod 664 /var/www/html/database/database.sqlite

# Optimization caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure generated cache files in bootstrap/cache are accessible by www-data
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "Personality 360 Assessment is ready on port ${PORT}."

# Hand over to Apache
exec apache2-foreground
