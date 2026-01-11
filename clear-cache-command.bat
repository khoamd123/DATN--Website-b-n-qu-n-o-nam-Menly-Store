@echo off
cd /d "C:\laragon\www\DATN--Website-b-n-qu-n-o-nam-Menly-Store"
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo Cache cleared successfully!




