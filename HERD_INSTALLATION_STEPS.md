# 🚀 Hướng dẫn cài đặt Laravel Herd cho DATN_Uniclubs

## 📥 Bước 1: Tải Laravel Herd
1. Truy cập: https://herd.laravel.com/
2. Click "Download for Windows"
3. Tải file cài đặt

## ⚙️ Bước 2: Cài đặt Laravel Herd
1. Chạy file cài đặt vừa tải
2. Làm theo hướng dẫn cài đặt
3. Khởi động Laravel Herd

## 🌐 Bước 3: Thêm dự án vào Herd
1. Mở Laravel Herd
2. Click "Add Site" (dấu +)
3. Chọn thư mục: `C:\xampp\htdocs\DATN_Uniclubs`
4. Site name: `uniclubs`
5. Click "Add Site"

## 📁 Bước 4: Tạo file .env
1. Copy nội dung file `env-herd-template.txt`
2. Tạo file `.env` trong thư mục dự án
3. Paste nội dung vào file `.env`

## 🗄️ Bước 5: Chạy lệnh setup
Mở terminal trong thư mục dự án và chạy:

```bash
# Tạo APP_KEY
php artisan key:generate

# Chạy migrations và seeders
php artisan migrate:fresh --seed
```

## 🎯 Bước 6: Truy cập Admin Panel
- **URL:** http://uniclubs.test/login
- **Email:** nguyenvana@example.com
- **Password:** password

## ✅ Kiểm tra hoạt động
1. Truy cập http://uniclubs.test
2. Nếu thấy trang welcome → OK
3. Truy cập http://uniclubs.test/login
4. Đăng nhập với thông tin admin

## 🔧 Nếu gặp lỗi
- Kiểm tra Herd đã chạy chưa
- Kiểm tra database connection
- Restart Herd nếu cần

## 📞 Hỗ trợ
Nếu gặp vấn đề, hãy báo lại lỗi cụ thể!
