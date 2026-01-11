@echo off
cd /d "%~dp0"
echo ========================================
echo Tạo bảng club_payment_qrs
echo ========================================
echo.

REM Thử tìm PHP trong Laragon
set PHP_PATH=
if exist "C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe
) else if exist "C:\laragon\bin\php\php-8.2\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.2\php.exe
) else if exist "C:\laragon\bin\php\php-8.0\php.exe" (
    set PHP_PATH=C:\laragon\bin\php\php-8.0\php.exe
) else (
    set PHP_PATH=php
)

echo Đang sử dụng PHP: %PHP_PATH%
echo.

echo [Bước 1] Chạy artisan command để tạo bảng...
%PHP_PATH% artisan create:club-payment-qr-table

echo.
echo ========================================
echo Hoàn tất!
echo ========================================
echo.
echo Nếu lệnh trên báo lỗi, hãy thử cách khác:
echo 1. Truy cập: http://localhost/setup-club-payment-qr-table
echo 2. Hoặc chạy SQL trong phpMyAdmin: create_club_payment_qrs_table_simple.sql
echo.
pause

