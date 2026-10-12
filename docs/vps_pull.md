git pull origin main

php artisan optimize:clear
php artisan migrate                     # → jalankan semua migration baru
php artisan db:seed --class=TransaksiPermissionSeeder  # → assign sidebar permission

# Buka browser → login → sidebar muncul semua menu ✓