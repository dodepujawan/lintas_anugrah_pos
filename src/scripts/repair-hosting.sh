#!/usr/bin/env bash
set -euo pipefail

HESTIA_USER="admin"
DOMAIN="lintasgo.duckdns.org"
APP_DIR="/home/admin/web/lintasgo.duckdns.org/public_html"
EXPECTED_DOCROOT="${APP_DIR}/public"
HESTIA_BIN="/usr/local/hestia/bin"

echo "[1/7] Memeriksa struktur Laravel..."
test -f "${APP_DIR}/artisan" || { echo "ERROR: artisan tidak ditemukan di ${APP_DIR}"; exit 1; }
test -f "${EXPECTED_DOCROOT}/index.php" || { echo "ERROR: public/index.php tidak ditemukan"; exit 1; }

echo "[2/7] Mereset document root Hestia..."
"${HESTIA_BIN}/v-change-web-domain-docroot" "${HESTIA_USER}" "${DOMAIN}" default
"${HESTIA_BIN}/v-change-web-domain-docroot" "${HESTIA_USER}" "${DOMAIN}" "${DOMAIN}" public yes

APACHE_CONF="/home/${HESTIA_USER}/conf/web/${DOMAIN}/apache2.conf"
APACHE_SSL_CONF="/home/${HESTIA_USER}/conf/web/${DOMAIN}/apache2.ssl.conf"

echo "[3/7] Memverifikasi document root..."
grep -F "DocumentRoot ${EXPECTED_DOCROOT}" "${APACHE_CONF}" >/dev/null \
    || { echo "ERROR: DocumentRoot HTTP tidak tepat"; grep "DocumentRoot" "${APACHE_CONF}"; exit 1; }
grep -F "DocumentRoot ${EXPECTED_DOCROOT}" "${APACHE_SSL_CONF}" >/dev/null \
    || { echo "ERROR: DocumentRoot HTTPS tidak tepat"; grep "DocumentRoot" "${APACHE_SSL_CONF}"; exit 1; }
grep "DocumentRoot" "${APACHE_CONF}"
grep "DocumentRoot" "${APACHE_SSL_CONF}"

echo "[4/7] Memperbaiki permission folder runtime Laravel..."
chown -R "${HESTIA_USER}:${HESTIA_USER}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
find "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" -type d -exec chmod 775 {} \;
find "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache" -type f -exec chmod 664 {} \;

echo "[5/7] Membersihkan cache Laravel..."
cd "${APP_DIR}"
sudo -u "${HESTIA_USER}" php artisan optimize:clear

echo "[6/7] Memeriksa dan me-restart layanan..."
apachectl configtest
PHP_FPM_SERVICE="$(systemctl list-units --type=service --state=running --no-legend 'php*-fpm.service' 2>/dev/null | awk 'NR == 1 { print $1 }')"
if [ -n "${PHP_FPM_SERVICE}" ]; then
    systemctl restart "${PHP_FPM_SERVICE}"
else
    echo "PERINGATAN: layanan PHP-FPM aktif tidak ditemukan; restart dilewati."
fi
systemctl restart apache2
systemctl restart nginx

echo "[7/7] Menguji health endpoint..."
HTTP_STATUS="$(curl -k -sS -o /dev/null -w '%{http_code}' "https://${DOMAIN}/up")"
echo "HTTP status: ${HTTP_STATUS}"

if [ "${HTTP_STATUS}" != "200" ]; then
    echo "ERROR: Health check belum 200. Log Apache terbaru:"
    tail -n 30 "/var/log/apache2/domains/${DOMAIN}.error.log" || true
    exit 1
fi

echo "OK: https://${DOMAIN}/up merespons HTTP 200."
