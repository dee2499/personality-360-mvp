#!/bin/bash
set -e

# Port configuration (Render passes PORT as an environment variable, defaults to 10000)
PORT="${PORT:-10000}"
sed -ri -e "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -ri -e "s/:80/:${PORT}/g" /etc/apache2/sites-available/*.conf

# Ensure SQLite file exists and permissions are intact
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Run database migrations
php artisan migrate --force

# Seed database if no users exist yet
USER_COUNT=$(php artisan tinker --execute 'echo \App\Models\User::count();' 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "First boot: Seeding default database accounts and MVP demo survey..."
    php artisan db:seed --force
fi

# Optimization caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Personality 360 Assessment is ready on port ${PORT}."

# Hand over to Apache
exec apache2-foreground
