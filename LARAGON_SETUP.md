# 🚀 Hướng dẫn setup DATN_Uniclubs với Laragon

## 📥 Cài đặt Laragon

1. **Tải Laragon:** https://laragon.org/download/
2. **Cài đặt** phiên bản Full (có sẵn PHP 8.1+)
3. **Khởi động Laragon**

## 📁 Di chuyển dự án

1. **Copy thư mục dự án** vào `C:\laragon\www\DATN_Uniclubs`
2. **Hoặc di chuyển** từ `C:\xampp\htdocs\DATN_Uniclubs` sang `C:\laragon\www\DATN_Uniclubs`

## ⚙️ Cấu hình dự án

### Bước 1: Cập nhật file .env
```env
APP_NAME="CLB System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://uniclubs.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniclubs
DB_USERNAME=root
DB_PASSWORD=

# Laragon thường không cần password cho MySQL
```

### Bước 2: Tạo Virtual Host
1. **Mở Laragon**
2. **Right-click** vào Laragon system tray
3. **Menu > Quick add > Virtual Host**
4. **Nhập:** `uniclubs` (tên project)
5. **Chọn:** `C:\laragon\www\DATN_Uniclubs`
6. **Click OK**

### Bước 3: Khởi động services
1. **Click "Start All"** trong Laragon
2. **Hoặc click** MySQL và Apache

## 🔧 Setup dự án

Mở terminal trong thư mục dự án và chạy:

```bash
# Cài đặt dependencies
composer install

# Tạo APP_KEY
php artisan key:generate

# Chạy migrations và seeders
php artisan migrate:fresh --seed
```

## 🌐 Truy cập dự án

- **URL chính:** http://uniclubs.test
- **Admin Panel:** http://uniclubs.test/admin
- **Đăng nhập:** http://uniclubs.test/login

## 🔑 Thông tin đăng nhập

### Admin
- **Email:** nguyenvana@example.com
- **Password:** password

### User thường
- **Email:** tranthib@example.com
- **Password:** password

## 🎯 Lợi ích của Laragon

- ✅ **PHP 8.1+** tự động
- ✅ **MySQL** tích hợp sẵn
- ✅ **Virtual Host** dễ dàng
- ✅ **URL đẹp** (uniclubs.test)
- ✅ **SSL** tự động
- ✅ **Nginx/Apache** tùy chọn
- ✅ **Redis, Memcached** sẵn có

## 🔧 Troubleshooting

### Nếu không truy cập được:
1. **Kiểm tra Laragon** đã Start All chưa
2. **Kiểm tra Virtual Host** đã tạo chưa
3. **Thử URL:** http://localhost/uniclubs/public

### Nếu lỗi database:
1. **Kiểm tra MySQL** đã start chưa
2. **Tạo database** `uniclubs` trong phpMyAdmin
3. **Kiểm tra** thông tin database trong `.env`

### Nếu lỗi composer:
```bash
# Xóa vendor và cài lại
rm -rf vendor
composer install --ignore-platform-reqs
```

## 📱 PhpMyAdmin

- **URL:** http://localhost/phpmyadmin
- **Username:** root
- **Password:** (để trống)

## 🎉 Hoàn thành!

Sau khi setup xong, bạn có thể:
1. **Truy cập admin panel** tại http://uniclubs.test/admin
2. **Đăng nhập** với tài khoản admin
3. **Bắt đầu sử dụng** các chức năng quản lý
4. **Deploy lên Git** cho team members

**Chúc bạn thành công! 🚀**
