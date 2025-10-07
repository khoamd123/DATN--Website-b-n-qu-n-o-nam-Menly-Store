# 🎯 DATN_Uniclubs - Hệ thống quản lý câu lạc bộ sinh viên

## 📋 Mô tả dự án

Hệ thống quản lý câu lạc bộ sinh viên được xây dựng bằng Laravel 10, cung cấp đầy đủ chức năng quản lý cho các câu lạc bộ trong trường đại học.

## ✨ Tính năng chính

### 🎮 Admin Panel
- **Dashboard** - Thống kê tổng quan hệ thống
- **Quản lý người dùng** - Xem, tìm kiếm, phân quyền
- **Quản lý câu lạc bộ** - Duyệt, từ chối, thay đổi trạng thái
- **Tài liệu học tập** - Quản lý documents
- **Quản lý quỹ** - Thống kê tài chính, quản lý giao dịch
- **Kế hoạch** - Quản lý sự kiện, lịch trình
- **Bài viết** - Quản lý posts và thông báo
- **Bình luận** - Xem, xóa bình luận
- **Phân quyền** - Cấp quyền admin, quản lý permissions

### 👥 Chức năng người dùng
- Đăng ký/đăng nhập
- Tham gia câu lạc bộ
- Đăng bài viết
- Bình luận
- Tham gia sự kiện

## 🛠️ Công nghệ sử dụng

- **Backend:** Laravel 10, PHP 8.1+
- **Frontend:** Bootstrap 5, Font Awesome
- **Database:** MySQL
- **Authentication:** Laravel Sanctum

## 📁 Cấu trúc dự án

```
DATN_Uniclubs/
├── app/
│   ├── Http/Controllers/
│   │   ├── AdminController.php      # Controller admin panel
│   │   └── AuthController.php       # Controller authentication
│   ├── Http/Middleware/
│   │   └── AdminMiddleware.php      # Middleware kiểm tra admin
│   └── Models/                      # Các model chính
├── resources/views/
│   ├── admin/                       # Views admin panel
│   │   ├── layouts/app.blade.php    # Layout chính
│   │   ├── dashboard.blade.php      # Dashboard
│   │   ├── users/index.blade.php    # Quản lý users
│   │   ├── clubs/index.blade.php    # Quản lý clubs
│   │   └── ...                      # Các trang khác
│   └── auth/
│       └── login.blade.php          # Trang đăng nhập
├── routes/
│   └── web.php                      # Routes web
└── database/
    ├── migrations/                  # Database migrations
    └── seeders/                     # Database seeders
```

## 🚀 Hướng dẫn cài đặt

### Yêu cầu hệ thống
- PHP 8.1 hoặc cao hơn
- Composer
- MySQL
- XAMPP/Laravel Herd

### Cài đặt

1. **Clone repository:**
```bash
git clone <repository-url>
cd DATN_Uniclubs
```

2. **Cài đặt dependencies:**
```bash
composer install
```

3. **Cấu hình environment:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Cấu hình database trong `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniclubs
DB_USERNAME=root
DB_PASSWORD=
```

5. **Chạy migrations và seeders:**
```bash
php artisan migrate:fresh --seed
```

6. **Khởi động server:**
```bash
php artisan serve
```

## 🔑 Thông tin đăng nhập

### Admin
- **Email:** nguyenvana@example.com
- **Password:** password

### User thường
- **Email:** tranthib@example.com
- **Password:** password

## 📱 Truy cập ứng dụng

- **Trang chủ:** http://localhost:8000
- **Admin Panel:** http://localhost:8000/admin
- **Đăng nhập:** http://localhost:8000/login

## 👥 Thành viên nhóm

- [Tên thành viên 1] - [Vai trò]
- [Tên thành viên 2] - [Vai trò]
- [Tên thành viên 3] - [Vai trò]

## 📋 Danh sách công việc

### ✅ Đã hoàn thành
- [x] Tạo cấu trúc dự án Laravel
- [x] Thiết kế database schema
- [x] Xây dựng Admin Panel hoàn chỉnh
- [x] Tạo hệ thống authentication
- [x] Implement các chức năng quản lý

### 🔄 Đang thực hiện
- [ ] Frontend cho người dùng
- [ ] API endpoints
- [ ] Testing

### 📝 Cần làm
- [ ] Giao diện người dùng
- [ ] Chức năng đăng ký câu lạc bộ
- [ ] Hệ thống thông báo
- [ ] Upload file
- [ ] Tối ưu hóa performance

## 🎯 Hướng dẫn cho thành viên

### Để bắt đầu làm việc:

1. **Clone project về máy**
2. **Cài đặt theo hướng dẫn trên**
3. **Tạo branch mới:** `git checkout -b feature/ten-chuc-nang`
4. **Làm việc trên branch đó**
5. **Commit và push:** `git push origin feature/ten-chuc-nang`
6. **Tạo Pull Request**

### Quy tắc làm việc:
- Sử dụng tiếng Việt trong commit message
- Code phải tuân thủ PSR-12
- Test trước khi commit
- Không commit file `.env` hoặc `vendor/`

## 📞 Liên hệ

- **Project Manager:** [Tên PM]
- **Email:** [email@example.com]
- **GitHub:** [github-username]

## 📄 License

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

---

**🎉 Chúc các bạn làm việc hiệu quả!**