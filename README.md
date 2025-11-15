# 🎓 DATN UniClubs - Hệ thống quản lý câu lạc bộ sinh viên

## 📖 Mô tả dự án

DATN UniClubs là hệ thống quản lý câu lạc bộ sinh viên được phát triển bằng Laravel 8, cung cấp giao diện quản trị cho admin và giao diện thân thiện cho sinh viên.

## ✨ Tính năng chính

### 🔐 Hệ thống phân quyền
- **Admin**: Quản lý toàn hệ thống
- **Trưởng CLB**: Quản lý câu lạc bộ
- **Phó CLB**: Hỗ trợ quản lý
- **Cán sự**: Thực hiện các nhiệm vụ
- **Thành viên**: Tham gia hoạt động

### 🏛️ Giao diện Admin
- Dashboard tổng quan
- Quản lý người dùng
- Phân quyền chi tiết
- Quản lý câu lạc bộ
- Tài liệu học tập
- Quản lý quỹ - Thống kê tài chính, quản lý giao dịch
- Kế hoạch - Quản lý sự kiện, lịch trình
- Bài viết - Quản lý posts và thông báo
- Bình luận - Xem, xóa bình luận
- Thống kê và báo cáo

### 🎓 Giao diện Sinh viên
- Dashboard cá nhân
- Tham gia câu lạc bộ
- Đăng ký sự kiện
- Quản lý hồ sơ
- Thông báo
- Đăng bài viết
- Bình luận

## 🛠️ Công nghệ sử dụng
- **Backend**: Laravel 8, PHP 8.0+
- **Frontend**: Blade Templates, Bootstrap 5, Chart.js
- **Database**: MySQL
- **Authentication**: Session-based
- **UI/UX**: Responsive Design, Modern UI

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
- PHP >= 8.0
- Composer
- MySQL
- XAMPP/WAMP (khuyến nghị)

### Bước 1: Clone repository
```bash
git clone https://github.com/khoamd123/DATN--Website-b-n-qu-n-o-nam-Menly-Store.git
cd DATN_Uniclubs
```

### Bước 2: Cài đặt dependencies
```bash
composer install
```

### Bước 3: Cấu hình environment
```bash
copy .env.example .env
php artisan key:generate
```

Chỉnh sửa file `.env`:
```env
APP_NAME="DATN UniClubs"
APP_URL=http://localhost/DATN_Uniclubs/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=datn_uniclubs
DB_USERNAME=root
DB_PASSWORD=
```

### Bước 4: Tạo database và chạy migration
```bash
php artisan migrate:fresh --seed
```

### Bước 5: Tạo storage link
```bash
php artisan storage:link
```

### Bước 6: Khởi động server
```bash
php artisan serve
```

## 🎯 Sử dụng

### Truy cập Admin Panel
```
http://localhost/DATN_Uniclubs/public/admin
```
- **Login**: admin / password (hoặc admin123)

### Truy cập Student Interface
```
http://localhost/DATN_Uniclubs/public/quick-login-student
```
- **Tự động login**: khoamdph31863@fpt.edu.vn

### Đăng ký sinh viên mới
```
http://localhost/DATN_Uniclubs/public/register
```

## 🔑 Thông tin đăng nhập

### Admin
- **Email:** admin@university.edu.vn
- **Password:** password

### User thường
- **Email:** tranthib@university.edu.vn
- **Password:** password

## 📊 Database Schema

### Bảng chính
- `users` - Thông tin người dùng
- `clubs` - Thông tin câu lạc bộ
- `club_members` - Thành viên câu lạc bộ
- `permissions` - Quyền hạn
- `user_permissions_club` - Phân quyền theo CLB
- `events` - Sự kiện
- `posts` - Bài viết
- `notifications` - Thông báo

## 🔧 Tính năng nổi bật

### 🎨 Giao diện hiện đại
- Responsive design
- Dark/Light theme
- Smooth animations
- User-friendly interface

### 🔐 Bảo mật
- Session-based authentication
- Role-based access control
- CSRF protection
- Input validation

### 📱 Responsive
- Mobile-friendly
- Tablet optimized
- Desktop experience

## 📝 API Endpoints

### Authentication
- `POST /login` - Đăng nhập
- `POST /register` - Đăng ký
- `POST /logout` - Đăng xuất

### Admin
- `GET /admin/dashboard` - Dashboard admin
- `GET /admin/users` - Quản lý người dùng
- `GET /admin/permissions` - Phân quyền

### Student
- `GET /student/dashboard` - Dashboard sinh viên
- `GET /student/clubs` - Danh sách CLB
- `GET /student/events` - Sự kiện

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

## 🤝 Đóng góp
1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📞 Liên hệ
- **Email**: your-email@example.com
- **Project Link**: [https://github.com/khoamd123/DATN--Website-b-n-qu-n-o-nam-Menly-Store](https://github.com/khoamd123/DATN--Website-b-n-qu-n-o-nam-Menly-Store)

## 🙏 Acknowledgments
- Laravel Framework
- Bootstrap
- Font Awesome
- Chart.js
- FPT University

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

⭐ **Nếu dự án hữu ích, hãy cho một star!** ⭐

**🎉 Chúc các bạn làm việc hiệu quả!**