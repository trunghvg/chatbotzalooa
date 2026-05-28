#!/bin/bash
# Script chay khi Railway khoi dong container

set -e

echo "=== ZaloOA Bot - Starting ==="

# Tao thu muc can thiet
mkdir -p writable/logs writable/cache writable/session writable/uploads
chmod -R 777 writable/

# Chay migration (tu dong tao bang)
echo "Running migrations..."
php spark migrate --all -n 2>/dev/null || echo "Migration warning (may already exist)"

# Chay seeder lan dau neu DB trong
echo "Seeding default data..."
php spark db:seed DefaultSettings 2>/dev/null || echo "Seeder skipped (already seeded)"
php spark db:seed KnowledgeBaseSeeder 2>/dev/null || echo "KB Seeder skipped"

echo "=== Starting PHP server on port $PORT ==="
exec php -S 0.0.0.0:$PORT -t public public/index.php
