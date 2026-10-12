$ErrorActionPreference = 'Stop'

$server = 'root@202.10.40.136'
$appDir = '/home/admin/web/lintasgo.duckdns.org/public_html'
$hestiaUser = 'admin'

$oldNumber = Read-Host 'Masukkan nomor jurnal lama, contoh JU260702001'
$oldNumber = $oldNumber.Trim().ToUpperInvariant()

if ([string]::IsNullOrWhiteSpace($oldNumber)) {
    throw 'Nomor jurnal belum diisi.'
}

if ($oldNumber -notmatch '^[A-Z]+\d+$') {
    throw "Format nomor jurnal tidak valid: $oldNumber"
}

Write-Host ''
Write-Host '[1/3] Mengecek command normalisasi di hosting...'
& ssh.exe $server "test -f '$appDir/artisan' && cd '$appDir' && sudo -u '$hestiaUser' php artisan list app | grep -F 'app:normalize-legacy-journal-numbers'"
if ($LASTEXITCODE -ne 0) {
    throw 'Command normalisasi belum ada di hosting. Deploy project terbaru dulu.'
}

Write-Host ''
Write-Host '[2/3] Dry-run di hosting, belum mengubah data...'
& ssh.exe $server "cd '$appDir' && sudo -u '$hestiaUser' php artisan app:normalize-legacy-journal-numbers --only='$oldNumber'"
if ($LASTEXITCODE -ne 0) {
    throw 'Dry-run gagal. Data belum diubah.'
}

Write-Host ''
Write-Host 'Periksa hasil dry-run di atas.'
$confirm = Read-Host 'Ketik Y lalu Enter untuk menyimpan perubahan di database hosting'

if ($confirm.Trim().ToUpperInvariant() -ne 'Y') {
    Write-Host 'Dibatalkan. Data hosting belum diubah.'
    exit 0
}

Write-Host ''
Write-Host '[3/3] Eksekusi perubahan di hosting...'
& ssh.exe $server "cd '$appDir' && sudo -u '$hestiaUser' php artisan app:normalize-legacy-journal-numbers --only='$oldNumber' --execute"
if ($LASTEXITCODE -ne 0) {
    throw 'Eksekusi gagal. Jika ada error, transaksi dibatalkan oleh sistem.'
}

Write-Host ''
Write-Host 'OK: normalisasi nomor jurnal hosting selesai.'
