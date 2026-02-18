#!/bin/bash
set -e

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "🚀 Caching configuration..."
php artisan optimize
php artisan view:cache

if [ ! -L public/storage ]; then
    echo "🔗 Linking storage..." 
    php artisan storage:link
fi

echo "📦 Running migrations..."
php artisan migrate:fresh --seed --force

echo "�🔥 Starting Supervisord..."
exec "$@"