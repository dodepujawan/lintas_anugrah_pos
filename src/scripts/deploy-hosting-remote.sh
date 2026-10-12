#!/usr/bin/env bash
set -euo pipefail

HESTIA_USER="admin"
DOMAIN="lintasgo.duckdns.org"
APP_DIR="/home/admin/web/lintasgo.duckdns.org/public_html"
ARCHIVE="/tmp/finledger-deploy.tar.gz"
EXPECTED_DOCROOT="${APP_DIR}/public"
APACHE_CONF="/home/${HESTIA_USER}/conf/web/${DOMAIN}/apache2.conf"
APACHE_SSL_CONF="/home/${HESTIA_USER}/conf/web/${DOMAIN}/apache2.ssl.conf"

test "$(id -u)" -eq 0 || { echo "ERROR: script harus dijalankan sebagai root"; exit 1; }
test -f "${ARCHIVE}" || { echo "ERROR: arsip deployment tidak ditemukan"; exit 1; }
test -f "${APP_DIR}/.env" || { echo "ERROR: .env hosting tidak ditemukan; deployment dibatalkan"; exit 1; }
test -f "${APP_DIR}/artisan" || { echo "ERROR: artisan hosting tidak ditemukan"; exit 1; }
grep -F "DocumentRoot ${EXPECTED_DOCROOT}" "${APACHE_CONF}" >/dev/null \
    || { echo "ERROR: DocumentRoot HTTP bukan ${EXPECTED_DOCROOT}; deployment dibatalkan"; exit 1; }
grep -F "DocumentRoot ${EXPECTED_DOCROOT}" "${APACHE_SSL_CONF}" >/dev/null \
    || { echo "ERROR: DocumentRoot HTTPS bukan ${EXPECTED_DOCROOT}; deployment dibatalkan"; exit 1; }

echo "[server 1/6] Mengekstrak source code tanpa menyentuh .env dan cache runtime..."
tar -xzf "${ARCHIVE}" -C "${APP_DIR}"
chown -R "${HESTIA_USER}:${HESTIA_USER}" "${APP_DIR}"
php -l "${APP_DIR}/app/Providers/AppServiceProvider.php"

echo "[server 2/6] Memastikan folder runtime Laravel tersedia..."
install -d -o "${HESTIA_USER}" -g "${HESTIA_USER}" -m 775 \
    "${APP_DIR}/storage/logs" \
    "${APP_DIR}/storage/framework/cache/data" \
    "${APP_DIR}/storage/framework/sessions" \
    "${APP_DIR}/storage/framework/views" \
    "${APP_DIR}/bootstrap/cache"

echo "[server 3/6] Memasang dependency PHP production..."
cd "${APP_DIR}"
if ! sudo -u "${HESTIA_USER}" composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction; then
    echo "ERROR: composer install gagal"
    exit 1
fi
if ! sudo -u "${HESTIA_USER}" php artisan about >/dev/null; then
    echo "ERROR: php artisan about gagal (cek .env / database / permission)"
    sudo -u "${HESTIA_USER}" php artisan about || true
    exit 1
fi

echo "[server 4/6] Membersihkan cache Laravel..."
if ! sudo -u "${HESTIA_USER}" php artisan optimize:clear; then
    echo "ERROR: php artisan optimize:clear gagal"
    exit 1
fi

echo "[server 5/6] Memperbaiki permission runtime..."
chown -R "${HESTIA_USER}:${HESTIA_USER}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
find "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" -type d -exec chmod 775 {} \;
find "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" -type f -exec chmod 664 {} \;

PHP_FPM_SERVICE="$(systemctl list-units --type=service --state=running --no-legend 'php*-fpm.service' 2>/dev/null | awk 'NR == 1 { print $1 }')"
if [ -n "${PHP_FPM_SERVICE}" ]; then
    echo "Restart ${PHP_FPM_SERVICE}..."
    systemctl restart "${PHP_FPM_SERVICE}"
else
    echo "PERINGATAN: layanan PHP-FPM aktif tidak ditemukan; restart dilewati."
fi

echo "[server 6/6] Health check..."
HTTP_STATUS="$(curl -k -sS -o /dev/null -w '%{http_code}' "https://${DOMAIN}/up" || echo "000")"
echo "HTTP status: ${HTTP_STATUS}"

# Jangan hapus script dulu sebelum evaluasi status, agar log tetap terbaca saat gagal
if [ "${HTTP_STATUS}" != "200" ]; then
    echo "ERROR: deployment selesai tetapi health check bukan HTTP 200"
    echo "Cek: https://${DOMAIN}/up dan storage/logs/laravel.log"
    rm -f "${ARCHIVE}" /tmp/deploy-hosting-remote.sh
    exit 1
fi

rm -f "${ARCHIVE}" /tmp/deploy-hosting-remote.sh
echo "OK: aplikasi merespons HTTP 200."
