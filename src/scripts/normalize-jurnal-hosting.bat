@echo off
setlocal

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0normalize-jurnal-hosting.ps1"

if errorlevel 1 (
    echo.
    echo NORMALISASI GAGAL. Baca pesan error di atas.
    echo Tekan tombol apa saja setelah mencatat atau mengambil screenshot error.
    pause
    exit /b 1
)

echo.
echo NORMALISASI SELESAI.
pause
