# Hướng dẫn chạy Migration để tạo bảng club_payment_qrs

## Lỗi hiện tại
```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'uniclubs.club_payment_qrs' doesn't exist
```

## Giải pháp

### Cách 1: Sử dụng file batch (Khuyến nghị - Windows)
Chạy file `run-migrations.bat` trong thư mục gốc của project bằng cách double-click vào file hoặc chạy từ Command Prompt.

### Cách 2: Chạy lệnh thủ công
Mở terminal/PowerShell trong thư mục project và chạy:

```bash
php artisan migrate
```

Hoặc chạy từng migration cụ thể:

```bash
php artisan migrate --path=database/migrations/2026_01_20_000001_create_club_payment_qrs_table.php
php artisan migrate --path=database/migrations/2026_01_20_000002_add_payment_fields_to_fund_transactions_table.php
```

### Cách 3: Nếu php không được nhận diện
1. Mở Command Prompt (cmd) hoặc PowerShell
2. Navigate đến thư mục Laragon PHP:
   ```bash
   cd C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64
   ```
3. Chạy migration:
   ```bash
   php.exe C:\laragon\www\DATN--Website-b-n-qu-n-o-nam-Menly-Store\artisan migrate
   ```

## Các bảng sẽ được tạo:
1. `club_payment_qrs` - Lưu thông tin QR code thanh toán của mỗi CLB
2. Các trường mới trong `fund_transactions`:
   - `payment_method` - Phương thức thanh toán
   - `transaction_code` - Mã giao dịch/Số bill
   - `payer_name` - Tên người nộp
   - `payer_phone` - Số điện thoại người nộp

## Sau khi chạy migration
- Refresh lại trang để kiểm tra
- Leader có thể truy cập `/student/club-management/{club}/payment-qr` để thêm QR code
- Thành viên có thể truy cập `/student/club-management/fund-deposit?club={club}` để nộp quỹ




