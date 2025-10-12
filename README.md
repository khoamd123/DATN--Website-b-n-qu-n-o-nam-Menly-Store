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
- Thống kê và báo cáo

### 🎓 Giao diện Sinh viên
- Dashboard cá nhân
- Tham gia câu lạc bộ
- Đăng ký sự kiện
- Quản lý hồ sơ
- Thông báo

## 🛠️ Công nghệ sử dụng
- **Backend**: Laravel 8, PHP 8.0+
- **Frontend**: Blade Templates, Bootstrap 5, Chart.js
- **Database**: MySQL
- **Authentication**: Session-based
- **UI/UX**: Responsive Design, Modern UI

## 🚀 Cài đặt

### Yêu cầu hệ thống
- PHP >= 8.0
- Composer
- MySQL
- XAMPP/WAMP (khuyến nghị)

### Bước 1: Clone repository
```bash
git clone https://github.com/username/DATN_Uniclubs.git
cd DATN_Uniclubs
```

### Bước 2: Cài đặt dependencies
```bash
composer install
```

### Bước 3: Cấu hình environment
```bash
copy .env.example .env
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

## 🎯 Sử dụng

### Truy cập Admin Panel
```
http://localhost/DATN_Uniclubs/public/admin
```
- **Login**: admin / admin123

### Truy cập Student Interface
```
http://localhost/DATN_Uniclubs/public/quick-login-student
```
- **Tự động login**: khoamdph31863@fpt.edu.vn

### Đăng ký sinh viên mới
```
http://localhost/DATN_Uniclubs/public/register
```

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

## 🤝 Đóng góp

1. Fork repository
2. Tạo feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Tạo Pull Request

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

## 👨‍💻 Tác giả

**DATN Student** - *Laravel Developer*

## 📞 Liên hệ

- **Email**: your-email@example.com
- **Project Link**: [https://github.com/username/DATN_Uniclubs](https://github.com/username/DATN_Uniclubs)

## 🙏 Acknowledgments

- Laravel Framework
- Bootstrap
- Font Awesome
- Chart.js
- FPT University

---

⭐ **Nếu dự án hữu ích, hãy cho một star!** ⭐