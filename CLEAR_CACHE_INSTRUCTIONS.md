# Hướng dẫn Clear Cache để sửa lỗi "Target class [club_role] does not exist"

## Vấn đề
Lỗi xảy ra vì Laravel không thể resolve middleware alias `club_role` do cache chưa được clear.

## Giải pháp

### Cách 1: Sử dụng file batch (Windows)
Chạy file `clear-cache-command.bat` trong thư mục gốc của project.

### Cách 2: Chạy lệnh thủ công
Mở terminal/PowerShell trong thư mục project và chạy các lệnh sau:

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

### Cách 3: Nếu vẫn còn lỗi
1. Xóa thủ công các file cache:
   - `bootstrap/cache/config.php` (nếu có)
   - `bootstrap/cache/routes.php` (nếu có)
   - `storage/framework/cache/data/*` (xóa tất cả file trong thư mục này)

2. Sau đó chạy lại:
```bash
php artisan config:cache
php artisan route:cache
```

## Kiểm tra
Sau khi clear cache, kiểm tra:
1. File `app/Http/Kernel.php` có dòng: `'club_role' => \App\Http\Middleware\ClubRoleMiddleware::class,`
2. File `app/Http/Middleware/ClubRoleMiddleware.php` tồn tại và có namespace đúng: `App\Http\Middleware`

## Lưu ý
- Nếu đang chạy server (php artisan serve), cần restart lại server sau khi clear cache.
- Nếu dùng Laravel Valet hoặc Homestead, có thể cần restart service.




