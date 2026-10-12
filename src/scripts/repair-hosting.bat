@echo off
setlocal

set "SERVER=root@202.10.40.136"
set "SCRIPT=%~dp0repair-hosting.sh"

if not exist "%SCRIPT%" (
    echo ERROR: File repair-hosting.sh tidak ditemukan.
    exit /b 1
)

echo Menghubungkan ke %SERVER%...
echo Password SSH akan diminta oleh SSH dan tidak disimpan oleh script.
ssh "%SERVER%" "bash -s" < "%SCRIPT%"

if errorlevel 1 (
    echo.
    echo PERBAIKAN GAGAL. Baca pesan error di atas.
    exit /b 1
)

echo.
echo PERBAIKAN SELESAI.
pause

