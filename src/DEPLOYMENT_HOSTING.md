# Catatan Deployment Hosting FinLedger

## Lokasi aplikasi

### Komputer lokal

```text
C:\LARAVEL_APP\akuntansi
```

### VPS / HestiaCP

```text
/home/admin/web/lintasgo.duckdns.org/public_html
```

Document root Hestia/Apache harus tepat:

```text
/home/admin/web/lintasgo.duckdns.org/public_html/public
```

Jangan sampai berubah menjadi `public/public` atau `public/public/public`.

## Folder yang tidak boleh di-upload dari lokal

Jangan upload atau menimpa isi folder berikut dari komputer Windows:

```text
storage/logs/
storage/framework/
bootstrap/cache/
```

Lokasi lokal lengkap:

```text
C:\LARAVEL_APP\akuntansi\storage\logs
C:\LARAVEL_APP\akuntansi\storage\framework
C:\LARAVEL_APP\akuntansi\bootstrap\cache
```

Folder tersebut harus dibuat dan dikelola langsung oleh Laravel di VPS:

```text
/home/admin/web/lintasgo.duckdns.org/public_html/storage/logs
/home/admin/web/lintasgo.duckdns.org/public_html/storage/framework
/home/admin/web/lintasgo.duckdns.org/public_html/bootstrap/cache
```

## Setelah upload perubahan

Jalankan melalui SSH:

```bash
cd /home/admin/web/lintasgo.duckdns.org/public_html
sudo -u admin php artisan optimize:clear
```

Jangan mengubah Custom document root Hestia jika aplikasi sedang berjalan normal.

## Deployment otomatis dari Windows

Jalankan dari root proyek:

```bat
scripts\deploy-hosting.bat
```

Script deployment otomatis tidak meng-upload atau menimpa:

```text
.env
vendor/
node_modules/
storage/logs/
storage/framework/
storage/app/
bootstrap/cache/
```

Dependency PHP production dipasang langsung di VPS menggunakan Composer. Script tidak menjalankan migrasi database dan tidak mengubah document root Hestia. Deployment dibatalkan otomatis jika document root tidak tepat atau sintaks `AppServiceProvider.php` tidak valid.

## Pemulihan otomatis

Dari Windows, jalankan:

```text
scripts\repair-hosting.bat
```

Gunakan hanya jika masalahnya berkaitan dengan document root, permission folder runtime, cache Laravel, atau layanan web.
