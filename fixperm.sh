#!/bin/bash

echo "🚀 FIX PERMISSION + CLEAR CACHE"

docker compose exec app bash -c "
cd src &&
chown -R www-data:www-data storage bootstrap/cache &&
chmod -R 775 storage bootstrap/cache &&
php artisan optimize:clear &&
php artisan config:cache &&
php artisan route:cache &&
php artisan view:cache
"

echo "✅ SELESAI"
# chmod +x fixperm.sh (untuk mengaktin)
# ./fixperm.sh (running fixprem)