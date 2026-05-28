#!/bin/bash
# Script chay khi Railway khoi dong container

set -e

echo "=== ZaloOA Bot - Starting ==="

# Tao thu muc can thiet
mkdir -p writable/logs writable/cache writable/session writable/uploads
chmod -R 777 writable/

# Tao bang DB tu dong (IF NOT EXISTS - an toan khi chay lai nhieu lan)
echo "Running database migration..."
php database/migrate.php

echo "=== Starting PHP server on port $PORT ==="
exec php -S 0.0.0.0:$PORT -t public public/index.php
