# Luồng hoạt động hệ thống thanh toán online

## 📋 Tổng quan

Hệ thống thanh toán online tích hợp với VNPay, cho phép người dùng thanh toán trực tuyến và tự động cập nhật vào hệ thống quỹ.

## 🔄 Luồng hoạt động chi tiết

### **Bước 1: Người dùng tạo thanh toán**

```
User → Click "Đóng góp quỹ online" 
     → Route: /payment/create?fund_id=X&club_id=Y&payment_type=fund_contribution
     → PaymentController@create
     → Hiển thị form thanh toán (payments/create.blade.php)
```

**Điều kiện:**
- User phải đăng nhập (session có user_id)
- Có thông tin quỹ/CLB/sự kiện (nếu có)

---

### **Bước 2: Submit form thanh toán**

```
User điền form → Submit
     → Route: POST /payment/store
     → PaymentController@store
```

**Xử lý:**
1. **Validate dữ liệu:**
   - Số tiền ≥ 1,000 VNĐ
   - Loại thanh toán hợp lệ
   - Phương thức thanh toán (vnpay)

2. **Tạo Payment record:**
   ```php
   Payment::create([
       'user_id' => $userId,
       'fund_id' => $request->fund_id,
       'amount' => $request->amount,
       'payment_method' => 'vnpay',
       'payment_type' => 'fund_contribution',
       'status' => 'pending',
       'expires_at' => now()->addHours(24) // Hết hạn sau 24h
   ]);
   ```
   - Tự động tạo `payment_code` (ví dụ: PAY20260108ABC12345)

3. **Tạo URL thanh toán VNPay:**
   ```php
   VNPayService::createPaymentUrl($payment)
   ```
   - Tạo hash bảo mật với SHA512
   - Tạo URL với các tham số: amount, order_info, txn_ref, etc.
   - Lưu URL vào `payment.payment_url`

4. **Chuyển hướng đến VNPay:**
   ```php
   return redirect($paymentUrl);
   ```

---

### **Bước 3: Thanh toán trên VNPay**

```
User → VNPay Gateway
     → Chọn ngân hàng/phương thức
     → Nhập thông tin thanh toán
     → Xác nhận OTP
     → VNPay xử lý thanh toán
```

**VNPay xử lý:**
- Kiểm tra số dư tài khoản
- Xác thực thông tin
- Trừ tiền từ tài khoản người dùng
- Tạo mã giao dịch (vnp_TransactionNo)

---

### **Bước 4: Callback từ VNPay**

```
VNPay → POST/GET /payment/vnpay/return
      → PaymentController@vnpayReturn
```

**Xử lý callback:**

1. **Xác thực callback:**
   ```php
   VNPayService::verifyCallback($inputData)
   ```
   - Kiểm tra hash (vnp_SecureHash)
   - Đảm bảo dữ liệu không bị giả mạo

2. **Xử lý kết quả:**
   ```php
   VNPayService::processCallback($inputData)
   ```
   - Kiểm tra `vnp_ResponseCode`:
     - `00` = Thành công
     - Khác = Thất bại (có mã lỗi cụ thể)

3. **Nếu thành công:**
   ```php
   // Đánh dấu payment thành công
   $payment->markAsCompleted($transactionId, $callbackData);
   
   // Tạo giao dịch quỹ tự động
   FundTransaction::create([
       'fund_id' => $payment->fund_id,
       'type' => 'income',
       'amount' => $payment->amount,
       'status' => 'approved', // Tự động duyệt
       ...
   ]);
   
   // Cập nhật số dư quỹ
   $fund->updateCurrentAmount();
   ```

4. **Nếu thất bại:**
   ```php
   $payment->markAsFailed($errorMessage);
   ```

5. **Redirect:**
   - Thành công → `/payment/success/{id}`
   - Thất bại → `/payment/failed`

---

### **Bước 5: Hiển thị kết quả**

**Trang thành công (`payments/success.blade.php`):**
- Hiển thị thông tin thanh toán
- Mã thanh toán, số tiền, mã giao dịch
- Link xem lịch sử thanh toán
- Link xem giao dịch quỹ (nếu có)

**Trang thất bại (`payments/failed.blade.php`):**
- Hiển thị lý do thất bại
- Nút thử lại
- Link về trang chủ

---

## 🔐 Bảo mật

### **1. Hash Verification:**
- VNPay gửi kèm `vnp_SecureHash` trong callback
- Hệ thống tính lại hash từ dữ liệu + secret key
- So sánh để đảm bảo dữ liệu không bị giả mạo

### **2. Transaction Lock:**
- Sử dụng DB Transaction để đảm bảo tính nhất quán
- Tránh race condition khi nhiều request đồng thời

### **3. Expiration:**
- Thanh toán hết hạn sau 24 giờ
- Không thể thanh toán sau khi hết hạn

---

## 💰 Tích hợp với hệ thống quỹ

### **Khi thanh toán thành công:**

1. **Tự động tạo FundTransaction:**
   ```php
   FundTransaction {
       type: 'income',
       amount: payment.amount,
       status: 'approved', // Tự động duyệt
       source: 'Thanh toán online',
       ...
   }
   ```

2. **Cập nhật số dư quỹ:**
   ```php
   Fund::updateCurrentAmount()
   // current_amount = initial_amount + totalIncome - totalExpense
   ```

3. **Liên kết Payment với FundTransaction:**
   ```php
   payment.fund_transaction_id = fundTransaction.id
   ```

---

## 📊 Trạng thái thanh toán

| Trạng thái | Mô tả | Hành động |
|------------|-------|-----------|
| `pending` | Chờ thanh toán | Có thể thanh toán hoặc hủy |
| `processing` | Đang xử lý | Chờ callback từ VNPay |
| `completed` | Thành công | Đã tạo giao dịch quỹ |
| `failed` | Thất bại | Không tạo giao dịch quỹ |
| `cancelled` | Đã hủy | User hủy thanh toán |

---

## 🔍 Ví dụ thực tế

### **Scenario: Sinh viên đóng góp 100,000 VNĐ vào quỹ CLB**

1. **Sinh viên vào trang quản lý quỹ CLB**
   - Click "Đóng góp quỹ online"
   - URL: `/payment/create?fund_id=1&club_id=1&payment_type=fund_contribution`

2. **Điền form:**
   - Số tiền: 100,000 VNĐ
   - Loại: Đóng góp quỹ
   - Submit

3. **Hệ thống tạo Payment:**
   ```
   Payment {
       payment_code: "PAY20260108ABC12345",
       user_id: 5,
       fund_id: 1,
       amount: 100000,
       status: "pending"
   }
   ```

4. **Chuyển đến VNPay:**
   - User chọn ngân hàng
   - Nhập thông tin
   - Xác nhận OTP
   - VNPay trừ 100,000 VNĐ

5. **VNPay callback:**
   ```
   GET /payment/vnpay/return?
       vnp_ResponseCode=00&
       vnp_TransactionNo=12345678&
       vnp_Amount=10000000&
       vnp_SecureHash=...
   ```

6. **Hệ thống xử lý:**
   - Xác thực hash ✅
   - Đánh dấu payment = completed ✅
   - Tạo FundTransaction (income, 100,000 VNĐ, approved) ✅
   - Cập nhật số dư quỹ ✅

7. **Hiển thị trang thành công:**
   - "Thanh toán thành công!"
   - Mã thanh toán: PAY20260108ABC12345
   - Mã giao dịch: 12345678
   - Link xem giao dịch quỹ

---

## 🛠️ Các thành phần chính

### **1. Payment Model**
- Lưu trữ thông tin thanh toán
- Tự động tạo payment_code
- Quan hệ với User, Fund, Event, Club

### **2. VNPayService**
- Tạo URL thanh toán
- Xác thực callback
- Xử lý kết quả thanh toán

### **3. PaymentController**
- Quản lý luồng thanh toán
- Xử lý callback
- Tích hợp với hệ thống quỹ

### **4. Routes**
- `/payment/create` - Form tạo thanh toán
- `/payment/store` - Tạo và redirect đến VNPay
- `/payment/vnpay/return` - Callback từ VNPay
- `/payment/success/{id}` - Trang thành công
- `/payment/history` - Lịch sử thanh toán

---

## ⚠️ Lưu ý quan trọng

1. **Callback URL phải công khai:**
   - VNPay cần gọi được URL callback
   - Không được đặt sau firewall hoặc yêu cầu auth

2. **Hash Secret phải bảo mật:**
   - Không commit vào git
   - Lưu trong .env

3. **Xử lý timeout:**
   - Thanh toán hết hạn sau 24h
   - User có thể tạo thanh toán mới

4. **Idempotency:**
   - Kiểm tra payment đã completed chưa
   - Tránh xử lý callback nhiều lần

---

## 📈 Mở rộng

Có thể mở rộng để hỗ trợ:
- MoMo Payment
- ZaloPay
- PayPal
- Stripe
- Thanh toán QR Code

Bằng cách tạo Service mới tương tự VNPayService và cập nhật PaymentController.






