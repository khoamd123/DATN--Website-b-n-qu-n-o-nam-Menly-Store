# Hướng dẫn cấu hình thanh toán online

## Tổng quan

Hệ thống đã được tích hợp thanh toán online qua VNPay. Người dùng có thể:
- Đóng góp quỹ CLB qua thanh toán online
- Đăng ký tham gia sự kiện có phí
- Đóng phí CLB
- Xem lịch sử thanh toán

## Cấu hình VNPay

### 1. Đăng ký tài khoản VNPay

1. Truy cập: https://sandbox.vnpayment.vn/ (môi trường test) hoặc https://www.vnpayment.vn/ (môi trường production)
2. Đăng ký tài khoản merchant
3. Lấy các thông tin:
   - TMN Code (Terminal Code)
   - Hash Secret

### 2. Cấu hình trong file .env

Thêm các dòng sau vào file `.env`:

```env
# VNPay Configuration
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_TMN_CODE=YOUR_TMN_CODE
VNPAY_HASH_SECRET=YOUR_HASH_SECRET
VNPAY_RETURN_URL=http://localhost:8000/payment/vnpay/return
```

**Lưu ý:**
- Môi trường test: `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html`
- Môi trường production: `https://www.vnpayment.vn/paymentv2/vpcpay.html`
- Thay `YOUR_TMN_CODE` và `YOUR_HASH_SECRET` bằng thông tin từ VNPay
- Thay `http://localhost:8000` bằng domain thực tế của bạn

### 3. Chạy migration

```bash
php artisan migrate
```

## Sử dụng

### Tạo thanh toán từ code

```php
use App\Models\Payment;

$payment = Payment::create([
    'user_id' => $userId,
    'fund_id' => $fundId, // Optional
    'amount' => 100000, // Số tiền (VNĐ)
    'payment_method' => 'vnpay',
    'payment_type' => 'fund_contribution',
    'description' => 'Đóng góp quỹ CLB',
]);

// Lấy URL thanh toán
$vnpayService = app(\App\Services\VNPayService::class);
$paymentUrl = $vnpayService->createPaymentUrl($payment);

// Chuyển hướng đến cổng thanh toán
return redirect($paymentUrl);
```

### Tạo thanh toán từ route

```php
// Redirect đến form tạo thanh toán
return redirect()->route('payments.create', [
    'fund_id' => $fundId,
    'club_id' => $clubId,
    'amount' => 100000,
    'payment_type' => 'fund_contribution'
]);
```

## Routes

- `GET /payment/create` - Form tạo thanh toán
- `POST /payment/store` - Tạo thanh toán và chuyển đến cổng thanh toán
- `GET /payment/vnpay/return` - Callback từ VNPay (tự động xử lý)
- `GET /payment/success/{id}` - Trang thanh toán thành công
- `GET /payment/failed` - Trang thanh toán thất bại
- `GET /payment/history` - Lịch sử thanh toán của user
- `POST /payment/{id}/cancel` - Hủy thanh toán

## Tích hợp với hệ thống quỹ

Khi thanh toán thành công:
1. Tự động tạo giao dịch quỹ (FundTransaction) với type = 'income'
2. Tự động duyệt giao dịch (status = 'approved')
3. Tự động cập nhật số dư quỹ
4. Liên kết Payment với FundTransaction

## Kiểm tra

1. Tạo thanh toán test với số tiền nhỏ (ví dụ: 1,000 VNĐ)
2. Thanh toán qua VNPay sandbox
3. Kiểm tra callback được xử lý đúng
4. Kiểm tra giao dịch quỹ được tạo tự động
5. Kiểm tra số dư quỹ được cập nhật

## Xử lý lỗi

- Nếu callback không được xử lý: Kiểm tra URL return trong VNPay merchant
- Nếu hash không khớp: Kiểm tra VNPAY_HASH_SECRET trong .env
- Nếu không tạo được giao dịch quỹ: Kiểm tra fund_id có tồn tại không

## Mở rộng

Để thêm cổng thanh toán khác (MoMo, ZaloPay, etc.):
1. Tạo Service tương tự VNPayService
2. Thêm config trong `config/payment.php`
3. Cập nhật PaymentController để hỗ trợ cổng mới
4. Tạo routes callback cho cổng mới






