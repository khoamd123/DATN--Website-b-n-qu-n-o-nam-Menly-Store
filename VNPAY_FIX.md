# Hướng dẫn sửa lỗi VNPay Code 03

## Lỗi: "Dữ liệu gửi sang không đúng định dạng" (Code 03)

Lỗi này thường xảy ra khi:
1. **TMN Code hoặc Hash Secret chưa được cấu hình** trong file `.env`
2. **Thiếu hoặc sai định dạng tham số** gửi đến VNPay
3. **Hash không đúng** do Hash Secret sai

## Cách sửa

### 1. Kiểm tra file .env

Đảm bảo các thông tin sau đã được cấu hình:

```env
VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_TMN_CODE=YOUR_TMN_CODE_HERE
VNPAY_HASH_SECRET=YOUR_HASH_SECRET_HERE
VNPAY_RETURN_URL=http://localhost:8000/payment/vnpay/return
```

**Lưu ý quan trọng:**
- `VNPAY_TMN_CODE` và `VNPAY_HASH_SECRET` **KHÔNG ĐƯỢC ĐỂ TRỐNG**
- Nếu để trống, hệ thống sẽ báo lỗi trước khi gửi đến VNPay
- Lấy thông tin này từ tài khoản VNPay của bạn

### 2. Lấy thông tin từ VNPay

**Môi trường test (Sandbox):**
1. Truy cập: https://sandbox.vnpayment.vn/
2. Đăng nhập hoặc đăng ký tài khoản
3. Vào phần **Thông tin kết nối** hoặc **API Integration**
4. Copy **TMN Code** và **Hash Secret**

**Môi trường production:**
1. Truy cập: https://www.vnpayment.vn/
2. Đăng nhập tài khoản merchant
3. Lấy thông tin từ phần cấu hình

### 3. Kiểm tra log

Sau khi cấu hình, thử tạo thanh toán lại. Nếu vẫn lỗi, kiểm tra file log:

```bash
tail -f storage/logs/laravel.log
```

Hoặc xem trong Laravel:
- Vào `storage/logs/laravel.log`
- Tìm dòng có "VNPay Payment URL created" hoặc "Error creating VNPay URL"

### 4. Test với dữ liệu mẫu

Nếu bạn đang test với VNPay Sandbox, có thể dùng thông tin mẫu:

```env
# THÔNG TIN NÀY CHỈ DÙNG ĐỂ TEST, KHÔNG DÙNG TRONG PRODUCTION
VNPAY_TMN_CODE=2QXUI4J4
VNPAY_HASH_SECRET=RAOCTRGKRHJDHDCOIFGURAQIZYGIOOAN
```

**Lưu ý:** Thông tin trên có thể đã hết hạn hoặc không hoạt động. Tốt nhất là đăng ký tài khoản riêng.

## Các cải tiến đã thực hiện

1. ✅ **Validation TMN Code và Hash Secret** - Kiểm tra trước khi tạo URL
2. ✅ **Validate số tiền** - Đảm bảo số tiền ≥ 10 VNĐ
3. ✅ **Giới hạn độ dài OrderInfo** - Tối đa 255 ký tự
4. ✅ **Loại bỏ giá trị rỗng** - Không gửi tham số rỗng đến VNPay
5. ✅ **Error handling** - Bắt lỗi và hiển thị thông báo rõ ràng
6. ✅ **Logging** - Ghi log để debug (chỉ trong môi trường debug)

## Kiểm tra nhanh

Chạy lệnh sau để kiểm tra cấu hình:

```bash
php artisan tinker
```

Sau đó chạy:

```php
config('payment.vnpay.tmn_code')
config('payment.vnpay.hash_secret')
```

Nếu trả về `null` hoặc chuỗi rỗng, nghĩa là chưa cấu hình trong `.env`.

## Liên hệ hỗ trợ

Nếu vẫn gặp lỗi sau khi đã cấu hình đúng:
- Email VNPay: hotrovnpay@vnpay.vn
- Hotline: 1900 545 436
- Website: https://www.vnpayment.vn/






