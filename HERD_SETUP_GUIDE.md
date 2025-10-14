# Hướng dẫn setup Laravel Herd cho dự án DATN_Uniclubs

## Bước 1: Cài đặt Laravel Herd
1. Tải từ: https://herd.laravel.com/
2. Cài đặt và khởi động Herd

## Bước 2: Thêm dự án vào Herd
1. Mở Laravel Herd
2. Click "Add Site" 
3. Chọn thư mục: `C:\xampp\htdocs\DATN_Uniclubs`
4. Site name: `uniclubs` (hoặc tên bạn muốn)
5. Click "Add Site"

## Bước 3: Truy cập dự án
- URL sẽ là: `http://uniclubs.test`
- Hoặc: `http://uniclubs.local`

## Bước 4: Cấu hình database
1. Herd sẽ tự động tạo MySQL database
2. Database name: `uniclubs`
3. Username: `root`
4. Password: `password`

## Bước 5: Chạy migrations
Mở terminal trong thư mục dự án và chạy:
```bash
php artisan migrate:fresh --seed
```

## Bước 6: Truy cập Admin Panel
- Login URL: `http://uniclubs.test/login`
- Admin email: `nguyenvana@example.com`
- Password: `password`

## Lưu ý:
- Herd sẽ tự động cài PHP 8.2+
- MySQL sẽ chạy tự động
- Không cần cấu hình XAMPP
