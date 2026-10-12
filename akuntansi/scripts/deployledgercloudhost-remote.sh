#!/usr/bin/env bash
set -euo pipefail

DOMAIN="ledger.lintasanugrah.my.id"
SITE_USER="ledger"
ARCHIVE="/tmp/finledger-ledgercloudhost-deploy.tar.gz"

SUDO=()
if [ "$(id -u)" -ne 0 ]; then
    if command -v sudo >/dev/null 2>&1 && sudo -n true 2>/dev/null; then
        SUDO=(sudo -n)
    fi
fi

run_as_site_user() {
    if [ "$(id -un)" = "${SITE_USER}" ]; then
        "$@"
    elif [ ${#SUDO[@]} -gt 0 ]; then
        "${SUDO[@]}" -u "${SITE_USER}" "$@"
    else
        "$@"
    fi
}

APP_DIR=""
for candidate in \
    "/home/ledger/htdocs/${DOMAIN}" \
    "/home/admin/htdocs/${DOMAIN}" \
    /home/*/htdocs/"${DOMAIN}"
do
    if [ -d "${candidate}" ]; then
        APP_DIR="${candidate}"
        break
    fi
done

if [ -z "${APP_DIR}" ]; then
    APP_DIR="/home/ledger/htdocs/${DOMAIN}"
    echo "PERINGATAN: folder CloudPanel untuk domain ${DOMAIN} belum ditemukan."
    echo "Membuat folder deploy awal: ${APP_DIR}"
    if [ ${#SUDO[@]} -gt 0 ]; then
        "${SUDO[@]}" mkdir -p "${APP_DIR}"
        "${SUDO[@]}" chown -R "${SITE_USER}:${SITE_USER}" "${APP_DIR}"
    else
        mkdir -p "${APP_DIR}"
    fi
fi

EXPECTED_DOCROOT="${APP_DIR}/public"

test -f "${ARCHIVE}" || { echo "ERROR: arsip deployment tidak ditemukan"; exit 1; }
echo "APP_DIR terdeteksi: ${APP_DIR}"
echo "Document root yang harus dipakai di CloudPanel: ${EXPECTED_DOCROOT}"
if [ ! -w "${APP_DIR}" ] && [ ${#SUDO[@]} -eq 0 ]; then
    echo "ERROR: user $(id -un) tidak punya akses tulis ke ${APP_DIR}, dan sudo tanpa password tidak tersedia."
    echo "Jalankan deploy memakai user SSH yang punya akses ke folder ini, atau beri akses sudo/passwordless sudo untuk user admin."
    exit 1
fi

echo "[server 1/6] Mengekstrak source code tanpa menyentuh .env dan cache runtime..."
echo "Isi awal arsip deployment:"
tar -tzf "${ARCHIVE}" | sed -n '1,20p'
if [ ${#SUDO[@]} -gt 0 ]; then
    "${SUDO[@]}" sh -c "cd '${APP_DIR}' && tar -xzf '${ARCHIVE}'"
    "${SUDO[@]}" chown -R "${SITE_USER}:${SITE_USER}" "${APP_DIR}"
else
    cd "${APP_DIR}"
    tar -xzf "${ARCHIVE}"
fi
echo "Folder aktif setelah extract:"
pwd
echo "Isi folder setelah extract:"
ls -la "${APP_DIR}" | sed -n '1,40p'

test -f "${APP_DIR}/artisan" || {
    echo "ERROR: artisan hosting tidak ditemukan setelah extract; deployment dibatalkan"
    echo "Isi folder ${APP_DIR}:"
    find "${APP_DIR}" -maxdepth 2 -type f 2>/dev/null | sort | sed -n '1,40p' || true
    exit 1
}
test -f "${APP_DIR}/app/Providers/AppServiceProvider.php" || {
    echo "ERROR: AppServiceProvider.php tidak ditemukan setelah extract; deployment dibatalkan"
    echo "Isi folder ${APP_DIR}:"
    find "${APP_DIR}" -maxdepth 3 -type f 2>/dev/null | sort | sed -n '1,60p' || true
    exit 1
}
php -l "${APP_DIR}/app/Providers/AppServiceProvider.php"
test -f "${APP_DIR}/.env" || {
    echo "ERROR: .env hosting belum ada di ${APP_DIR}/.env"
    echo "Source code sudah diupload. Untuk deploy pertama, buat .env di CloudPanel/VPS lalu jalankan deploy lagi."
    echo "Pastikan Document Root CloudPanel mengarah ke: ${EXPECTED_DOCROOT}"
    exit 1
}

echo "[server 2/6] Memastikan folder runtime Laravel tersedia..."
if [ ${#SUDO[@]} -gt 0 ]; then
    "${SUDO[@]}" install -d -o "${SITE_USER}" -g "${SITE_USER}" -m 775 \
        "${APP_DIR}/storage/logs" \
        "${APP_DIR}/storage/framework/cache/data" \
        "${APP_DIR}/storage/framework/sessions" \
        "${APP_DIR}/storage/framework/views" \
        "${APP_DIR}/bootstrap/cache"
else
    install -d -m 775 \
        "${APP_DIR}/storage/logs" \
        "${APP_DIR}/storage/framework/cache/data" \
        "${APP_DIR}/storage/framework/sessions" \
        "${APP_DIR}/storage/framework/views" \
        "${APP_DIR}/bootstrap/cache"
fi

echo "[server 3/6] Memasang dependency PHP production..."
cd "${APP_DIR}"
if ! run_as_site_user composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction; then
    echo "ERROR: composer install gagal"
    exit 1
fi
if ! run_as_site_user php artisan about >/dev/null; then
    echo "ERROR: php artisan about gagal (cek .env / database / permission)"
    run_as_site_user php artisan about || true
    exit 1
fi

echo "[server 4/6] Membersihkan cache Laravel..."
if ! run_as_site_user php artisan optimize:clear; then
    echo "ERROR: php artisan optimize:clear gagal"
    exit 1
fi

echo "[server 5/6] Memperbaiki permission runtime..."
if [ ${#SUDO[@]} -gt 0 ]; then
    "${SUDO[@]}" chown -R "${SITE_USER}:${SITE_USER}" "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
    "${SUDO[@]}" chmod -R ug+rwX "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
else
    chmod -R ug+rwX "${APP_DIR}/storage" "${APP_DIR}/bootstrap/cache"
fi

if command -v sudo >/dev/null 2>&1; then
    PHP_FPM_SERVICE="$(systemctl list-units --type=service --state=running --no-legend 'php*-fpm.service' 2>/dev/null | awk 'NR == 1 { print $1 }')"
    if [ -n "${PHP_FPM_SERVICE}" ] && sudo -n true 2>/dev/null; then
        echo "Restart ${PHP_FPM_SERVICE}..."
        sudo systemctl restart "${PHP_FPM_SERVICE}"
    else
        echo "PERINGATAN: restart PHP-FPM dilewati (sudo tanpa password tidak tersedia atau service tidak ditemukan)."
    fi
else
    echo "PERINGATAN: sudo tidak tersedia; restart PHP-FPM dilewati."
fi

echo "[server 6/6] Health check..."
HTTP_STATUS="$(curl -k -sS -o /dev/null -w '%{http_code}' "https://${DOMAIN}/up" || echo "000")"
echo "HTTP status: ${HTTP_STATUS}"

if [ "${HTTP_STATUS}" != "200" ]; then
    echo "ERROR: deployment selesai tetapi health check bukan HTTP 200"
    echo "Cek: https://${DOMAIN}/up dan storage/logs/laravel.log"
    rm -f "${ARCHIVE}" /tmp/deployledgercloudhost-remote.sh
    exit 1
fi

rm -f "${ARCHIVE}" /tmp/deployledgercloudhost-remote.sh
echo "OK: aplikasi merespons HTTP 200. Document root CloudPanel harus mengarah ke ${EXPECTED_DOCROOT}."
