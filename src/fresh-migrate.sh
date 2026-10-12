#!/bin/bash
# Script untuk fresh migration dengan auto-seed
# Gunakan: bash fresh-migrate.sh

echo "🔄 Running fresh migration with seeding..."
php artisan migrate:fresh --seed

echo ""
echo "✅ Database siap digunakan!"
echo "Admin user: admin@akuntansi.com"
echo "Password: password"
