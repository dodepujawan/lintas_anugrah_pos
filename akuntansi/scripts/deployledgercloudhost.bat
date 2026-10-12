@echo off
setlocal

echo Deploy FinLedger ke CloudPanel Ledger
echo Setiap kali dijalankan, Anda akan diminta password SSH admin.
echo Password TIDAK disimpan ke file.
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0deployledgercloudhost.ps1"

if errorlevel 1 (
    echo.
    echo DEPLOYMENT GAGAL. Baca pesan error di atas.
    echo Tekan tombol apa saja setelah mencatat atau mengambil screenshot error.
    pause
    exit /b 1
)

echo.
echo DEPLOYMENT SELESAI.
pause
