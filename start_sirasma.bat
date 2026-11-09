@echo off
title SIRASMA ULBI - Local Server
cd /d "C:\Proyek_III"
echo =========================================================
echo   SIRASMA ULBI - SISTEM RESERVASI ASRAMA MAHASISWA
echo   Universitas Logistik dan Bisnis Internasional (ULBI)
echo =========================================================
echo.
echo Server berjalan di: http://localhost:8000
echo Membuka browser...
start http://localhost:8000
echo.
echo Tekan Ctrl+C untuk menghentikan server.
echo.
php -S localhost:8000
pause
