#!/bin/sh
set -e

# Pastikan folder storage dan bootstrap/cache ada
mkdir -p /var/www/html/storage/app/private/articles \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cache Laravel di production jika APP_KEY tersedia
if [ -n "$APP_KEY" ]; then
    echo "Running production optimization..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Jalankan migrasi dan seed database otomatis jika DB_HOST dikonfigurasi
if [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
    echo "Running database seeders..."
    php artisan db:seed --force || true
fi

exec "$@"

