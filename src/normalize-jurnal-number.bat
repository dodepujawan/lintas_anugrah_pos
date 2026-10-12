@echo off
setlocal

cd /d "%~dp0"

echo ================================================
echo  NORMALISASI NOMOR JURNAL LAMA
echo ================================================
echo.
echo Contoh nomor lama: JU260702001
echo.

set /p OLD_NUMBER=Masukkan nomor jurnal lama: 

if "%OLD_NUMBER%"=="" (
    echo.
    echo Nomor jurnal belum diisi. Proses dibatalkan.
    pause
    exit /b 1
)

echo.
echo Tahap 1: cek rencana perubahan tanpa menyimpan data.
echo.
php artisan app:normalize-legacy-journal-numbers --only=%OLD_NUMBER%

if errorlevel 1 (
    echo.
    echo Dry-run gagal. Data belum diubah.
    pause
    exit /b 1
)

echo.
echo Periksa hasil di atas.
echo Ketik Y lalu Enter jika ingin menyimpan perubahan ke database.
echo Selain Y akan membatalkan proses.
echo.
set /p CONFIRM=Eksekusi sekarang? [Y/N]: 

if /I not "%CONFIRM%"=="Y" (
    echo.
    echo Proses dibatalkan. Data belum diubah.
    pause
    exit /b 0
)

echo.
echo Tahap 2: menyimpan perubahan.
echo.
php artisan app:normalize-legacy-journal-numbers --only=%OLD_NUMBER% --execute

if errorlevel 1 (
    echo.
    echo Eksekusi gagal. Jika ada error, transaksi dibatalkan oleh sistem.
    pause
    exit /b 1
)

echo.
echo Selesai.
pause
