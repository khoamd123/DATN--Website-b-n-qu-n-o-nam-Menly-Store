# CHƯƠNG X: TRIỂN KHAI HỆ THỐNG

## 1. TỔNG QUAN VỀ TRIỂN KHAI

### 1.1. Mục tiêu triển khai
Triển khai hệ thống quản lý câu lạc bộ sinh viên lên môi trường production nhằm:
- Đưa hệ thống vào sử dụng thực tế
- Đảm bảo tính ổn định và hiệu suất cao
- Bảo mật thông tin người dùng
- Dễ dàng bảo trì và nâng cấp

### 1.2. Môi trường triển khai
Hệ thống được triển khai trên:
- **Môi trường phát triển (Development)**: Laragon/XAMPP trên Windows
- **Môi trường production**: Linux Server (Ubuntu 20.04 LTS hoặc cao hơn)
- **Web Server**: Apache 2.4+ hoặc Nginx 1.18+
- **Database**: MySQL 8.0+ hoặc MariaDB 10.5+
- **PHP**: PHP 8.0 trở lên
- **Framework**: Laravel 8.x

## 2. YÊU CẦU HỆ THỐNG

### 2.1. Yêu cầu phần cứng tối thiểu (Development)
- **CPU**: Dual-core 2.0 GHz trở lên
- **RAM**: 4GB trở lên (khuyến nghị 8GB)
- **Ổ cứng**: 10GB trống trở lên
- **Hệ điều hành**: Windows 10/11, macOS, Linux

### 2.2. Yêu cầu phần cứng (Production)
- **CPU**: 4 cores trở lên
- **RAM**: 8GB trở lên (khuyến nghị 16GB)
- **Ổ cứng**: 50GB SSD trở lên
- **Băng thông**: 100Mbps trở lên

### 2.3. Yêu cầu phần mềm
- **PHP**: >= 8.0
  - Extension: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD/Imagick
- **Composer**: >= 2.0
- **MySQL/MariaDB**: >= 8.0 / >= 10.5
- **Node.js**: >= 16.x (cho frontend assets)
- **NPM/Yarn**: Latest version

## 3. CHUẨN BỊ TRIỂN KHAI

### 3.1. Chuẩn bị môi trường Development (Laragon)

#### 3.1.1. Cài đặt Laragon
1. Tải Laragon từ trang chủ: https://laragon.org/
2. Cài đặt Laragon với các tùy chọn mặc định
3. Khởi động Laragon, đảm bảo các service đã chạy:
   - Apache
   - MySQL
   - PHP

#### 3.1.2. Cấu hình Virtual Host
1. Mở Laragon, chọn "Menu" > "Preferences" > "Virtual hosts"
2. Tạo virtual host mới cho dự án
3. Trỏ document root đến thư mục `public` của dự án

Hoặc sử dụng file `setup_virtual_host.txt` có sẵn trong dự án.

### 3.2. Chuẩn bị môi trường Production

#### 3.2.1. Cài đặt LEMP Stack (Linux, Nginx, MySQL, PHP)

**Bước 1: Cập nhật hệ thống**
```bash
sudo apt update
sudo apt upgrade -y
```

**Bước 2: Cài đặt Nginx**
```bash
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

**Bước 3: Cài đặt MySQL**
```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
sudo systemctl start mysql
sudo systemctl enable mysql
```

**Bước 4: Cài đặt PHP và các extension**
```bash
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.0-fpm php8.0-mysql php8.0-xml php8.0-mbstring \
                 php8.0-curl php8.0-zip php8.0-gd php8.0-intl \
                 php8.0-bcmath php8.0-cli -y
```

**Bước 5: Cài đặt Composer**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

**Bước 6: Cài đặt Node.js và NPM**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

## 4. QUY TRÌNH TRIỂN KHAI

### 4.1. Triển khai trên môi trường Development

#### Bước 1: Clone/Copy dự án
```bash
# Nếu dùng Git
git clone <repository-url>
cd DATN--Website-b-n-qu-n-o-nam-Menly-Store

# Hoặc copy thư mục dự án vào thư mục www của Laragon
# Thường là: C:\laragon\www\
```

#### Bước 2: Cài đặt dependencies
```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node.js dependencies (nếu có)
npm install
```

#### Bước 3: Cấu hình môi trường
```bash
# Copy file .env.example thành .env
copy .env.example .env  # Windows
# hoặc
cp .env.example .env    # Linux/Mac

# Tạo application key
php artisan key:generate
```

#### Bước 4: Cấu hình file .env
Mở file `.env` và cấu hình:

```env
APP_NAME="DATN UniClubs"
APP_ENV=local
APP_KEY=base64:... (tự động tạo bởi key:generate)
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=datn_uniclubs
DB_USERNAME=root
DB_PASSWORD=

# Cấu hình khác...
```

#### Bước 5: Tạo database
```sql
-- Mở MySQL trong Laragon hoặc phpMyAdmin
CREATE DATABASE datn_uniclubs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Bước 6: Chạy migrations và seeders
```bash
# Chạy migrations
php artisan migrate

# Chạy seeders để tạo dữ liệu mẫu
php artisan db:seed

# Hoặc chạy cả hai cùng lúc
php artisan migrate:fresh --seed
```

#### Bước 7: Tạo symbolic link cho storage
```bash
php artisan storage:link
```

#### Bước 8: Build frontend assets (nếu có)
```bash
npm run dev
# hoặc
npm run build  # cho production
```

#### Bước 9: Cấu hình quyền thư mục (Linux/Mac)
```bash
# Đảm bảo các thư mục có quyền ghi
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### Bước 10: Truy cập ứng dụng
Mở trình duyệt và truy cập:
- **Local**: http://localhost/DATN--Website-b-n-qu-n-o-nam-Menly-Store/public
- **Virtual Host**: http://datn-uniclubs.test (nếu đã cấu hình)

### 4.2. Triển khai trên môi trường Production

#### Bước 1: Chuẩn bị server
- Đảm bảo server đã cài đặt LEMP stack như mục 3.2.1
- Cấu hình firewall để mở cổng 80 (HTTP) và 443 (HTTPS)

#### Bước 2: Tạo user và thư mục cho ứng dụng
```bash
# Tạo user mới (tùy chọn)
sudo adduser deploy
sudo usermod -aG www-data deploy

# Tạo thư mục cho ứng dụng
sudo mkdir -p /var/www/uniclubs
sudo chown -R deploy:www-data /var/www/uniclubs
```

#### Bước 3: Clone dự án
```bash
cd /var/www/uniclubs
sudo -u deploy git clone <repository-url> .
# hoặc sử dụng scp/sftp để upload code
```

#### Bước 4: Cài đặt dependencies
```bash
# Cài đặt PHP dependencies (production mode)
sudo -u deploy composer install --optimize-autoloader --no-dev

# Cài đặt Node.js dependencies
sudo -u deploy npm install
sudo -u deploy npm run build
```

#### Bước 5: Cấu hình .env cho production
```bash
sudo -u deploy cp .env.example .env
sudo -u deploy php artisan key:generate
```

Chỉnh sửa file `.env`:
```env
APP_NAME="DATN UniClubs"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniclubs_production
DB_USERNAME=uniclubs_user
DB_PASSWORD=strong_password_here

# Cấu hình cache và session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail configuration (nếu cần)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

#### Bước 6: Tạo database production
```bash
# Đăng nhập MySQL
sudo mysql -u root -p

# Tạo database và user
CREATE DATABASE uniclubs_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'uniclubs_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON uniclubs_production.* TO 'uniclubs_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Bước 7: Chạy migrations
```bash
cd /var/www/uniclubs
sudo -u deploy php artisan migrate --force
# KHÔNG chạy seeders trên production trừ khi cần dữ liệu mẫu
```

#### Bước 8: Tối ưu hóa cho production
```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

#### Bước 9: Cấu hình Nginx
Tạo file cấu hình Nginx:

```bash
sudo nano /etc/nginx/sites-available/uniclubs
```

Nội dung file:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/uniclubs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Tăng kích thước upload
    client_max_body_size 50M;
}
```

Kích hoạt site:
```bash
sudo ln -s /etc/nginx/sites-available/uniclubs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Bước 10: Cấu hình SSL với Let's Encrypt (khuyến nghị)
```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

#### Bước 11: Cấu hình quyền thư mục
```bash
sudo chown -R www-data:www-data /var/www/uniclubs
sudo chmod -R 755 /var/www/uniclubs
sudo chmod -R 775 /var/www/uniclubs/storage
sudo chmod -R 775 /var/www/uniclubs/bootstrap/cache
```

#### Bước 12: Cấu hình PHP-FPM
Chỉnh sửa file cấu hình PHP-FPM:
```bash
sudo nano /etc/php/8.0/fpm/php.ini
```

Một số cấu hình quan trọng:
```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
```

Khởi động lại PHP-FPM:
```bash
sudo systemctl restart php8.0-fpm
```

## 5. BẢO MẬT TRONG TRIỂN KHAI

### 5.1. Cấu hình bảo mật server

#### 5.1.1. Firewall (UFW)
```bash
# Cho phép SSH, HTTP, HTTPS
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

#### 5.1.2. Fail2Ban (bảo vệ chống brute force)
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

#### 5.1.3. Disable root login (khuyến nghị)
Chỉnh sửa `/etc/ssh/sshd_config`:
```
PermitRootLogin no
```

### 5.2. Cấu hình bảo mật Laravel

#### 5.2.1. File .env
- Không commit file `.env` lên Git
- Đặt quyền file `.env` là 600:
```bash
chmod 600 .env
```

#### 5.2.2. Cấu hình session
Trong file `.env`:
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true  # Chỉ sử dụng HTTPS
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

#### 5.2.3. Rate Limiting
Đã được cấu hình trong `app/Http/Kernel.php` và routes.

### 5.3. Backup dữ liệu

#### 5.3.1. Backup database tự động
Tạo script backup:

```bash
sudo nano /usr/local/bin/backup-uniclubs.sh
```

Nội dung:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/uniclubs"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="uniclubs_production"
DB_USER="uniclubs_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Xóa backup cũ hơn 30 ngày
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete
```

Cấp quyền thực thi:
```bash
sudo chmod +x /usr/local/bin/backup-uniclubs.sh
```

Thêm vào crontab (chạy mỗi ngày lúc 2 giờ sáng):
```bash
sudo crontab -e
# Thêm dòng:
0 2 * * * /usr/local/bin/backup-uniclubs.sh
```

#### 5.3.2. Backup files
```bash
# Backup thư mục storage
tar -czf /var/backups/uniclubs/storage_backup_$(date +%Y%m%d).tar.gz /var/www/uniclubs/storage
```

## 6. GIÁM SÁT VÀ BẢO TRÌ

### 6.1. Monitoring

#### 6.1.1. Log files
- **Application logs**: `storage/logs/laravel.log`
- **Nginx logs**: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- **PHP-FPM logs**: `/var/log/php8.0-fpm.log`
- **MySQL logs**: `/var/log/mysql/error.log`

Xem logs Laravel:
```bash
tail -f storage/logs/laravel.log
```

#### 6.1.2. System monitoring
Sử dụng các công cụ:
- **htop**: Giám sát CPU, RAM
- **df -h**: Kiểm tra dung lượng ổ cứng
- **netstat**: Kiểm tra kết nối mạng

### 6.2. Cập nhật và nâng cấp

#### 6.2.1. Cập nhật code
```bash
cd /var/www/uniclubs
sudo -u deploy git pull origin main
sudo -u deploy composer install --optimize-autoloader --no-dev
sudo -u deploy php artisan migrate --force
sudo -u deploy php artisan config:cache
sudo -u deploy php artisan route:cache
sudo -u deploy php artisan view:cache
```

#### 6.2.2. Cập nhật dependencies
```bash
# Kiểm tra dependencies cũ
composer outdated
npm outdated

# Cập nhật (cẩn thận trên production)
composer update
npm update
npm run build
```

### 6.3. Xử lý sự cố

#### 6.3.1. Ứng dụng không chạy
```bash
# Kiểm tra logs
tail -f storage/logs/laravel.log

# Kiểm tra permissions
ls -la storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 6.3.2. Database connection error
- Kiểm tra thông tin kết nối trong `.env`
- Kiểm tra MySQL service: `sudo systemctl status mysql`
- Kiểm tra firewall

#### 6.3.3. 500 Internal Server Error
- Kiểm tra logs
- Kiểm tra permissions thư mục storage và bootstrap/cache
- Kiểm tra cấu hình PHP

## 7. TỐI ƯU HÓA HIỆU SUẤT

### 7.1. Caching
- **Config cache**: `php artisan config:cache`
- **Route cache**: `php artisan route:cache`
- **View cache**: `php artisan view:cache`
- **OPcache**: Kích hoạt trong PHP.ini

### 7.2. Database optimization
- Sử dụng index cho các trường thường query
- Sử dụng eager loading để tránh N+1 query
- Phân trang cho danh sách lớn

### 7.3. Asset optimization
- Minify CSS/JS
- Enable Gzip compression trong Nginx
- Sử dụng CDN cho static files (nếu cần)

## 8. KẾT LUẬN

Quy trình triển khai hệ thống đã được trình bày chi tiết, bao gồm:
- Triển khai trên môi trường Development (Laragon)
- Triển khai trên môi trường Production (Linux Server)
- Cấu hình bảo mật
- Backup và monitoring
- Xử lý sự cố và tối ưu hóa

Hệ thống đã được triển khai thành công và sẵn sàng đưa vào sử dụng thực tế. Với các biện pháp bảo mật và monitoring đã được áp dụng, hệ thống sẽ hoạt động ổn định và an toàn.

## 9. PHỤ LỤC

### 9.1. Checklist triển khai
- [ ] Cài đặt môi trường (PHP, MySQL, Web Server)
- [ ] Clone/Copy code
- [ ] Cài đặt dependencies (Composer, NPM)
- [ ] Cấu hình .env
- [ ] Tạo database
- [ ] Chạy migrations
- [ ] Tạo storage link
- [ ] Cấu hình virtual host/server
- [ ] Cấu hình SSL (production)
- [ ] Cấu hình backup
- [ ] Cấu hình monitoring
- [ ] Test toàn bộ chức năng
- [ ] Đưa vào sử dụng

### 9.2. Thông tin liên hệ hỗ trợ
- **Tài liệu Laravel**: https://laravel.com/docs
- **Tài liệu Nginx**: https://nginx.org/en/docs/
- **Tài liệu MySQL**: https://dev.mysql.com/doc/

