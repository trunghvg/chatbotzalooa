#!/bin/bash
set +e

echo "=== ZaloOA Bot - Starting ==="

mkdir -p writable/logs writable/cache writable/session writable/uploads
chmod -R 777 writable/

echo "Running database migration..."
php database/migrate.php

# Update nginx listen port from Railway $PORT (default 8080)
APP_PORT=${PORT:-8080}
sed -i "s/listen 8080 default_server/listen $APP_PORT default_server/g" /etc/nginx/sites-available/default

echo "Starting PHP-FPM..."
php-fpm -D

echo "=== Starting nginx on port $APP_PORT ==="
exec nginx -g 'daemon off;'
