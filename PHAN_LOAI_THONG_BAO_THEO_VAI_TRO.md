# 📊 PHÂN LOẠI THÔNG BÁO THEO VAI TRÒ

**Ngày:** 9/1/2026  
**Mục đích:** Phân loại rõ ràng thông báo cho từng vai trò trong hệ thống

---

## 🎯 CÁC VAI TRÒ TRONG HỆ THỐNG

### 1. 👨‍💼 **ADMIN** (Quản trị viên)
- Quản lý toàn bộ hệ thống
- Phê duyệt kinh phí
- Giám sát hoạt động

### 2. 👑 **LEADER** (Trưởng CLB)
- Quản lý CLB
- Duyệt thành viên
- Quản lý quỹ
- Duyệt giao dịch

### 3. 👥 **VICE PRESIDENT** (Phó CLB)
- Hỗ trợ Leader
- Duyệt thành viên
- Quản lý hoạt động

### 4. 💰 **TREASURER** (Thủ quỹ)
- Quản lý quỹ CLB
- Duyệt giao dịch quỹ
- Xác nhận nộp quỹ

### 5. 👤 **MEMBER** (Thành viên bình thường)
- Tham gia hoạt động
- Xem thông tin CLB

---

## 📢 THÔNG BÁO CHO TỪNG VAI TRÒ

---

## 👨‍💼 1. ADMIN (CHỈ NHẬN THÔNG BÁO QUAN TRỌNG)

### ✅ CÁC THÔNG BÁO ADMIN NHẬN:

#### 💵 A. **YÊU CẦU CẤP KINH PHÍ MỚI** (Quan trọng nhất)
**Khi nào:** CLB gửi yêu cầu cấp kinh phí

**Thông tin:**
- 📝 Title: "Yêu cầu cấp kinh phí mới"
- 💰 Số tiền: XXX VNĐ
- 🏢 Từ CLB: {club_name}
- 📋 Mục đích: {title}
- 👤 Người gửi: {user_name}

**Icon:** 💵 `fa-hand-holding-usd` (màu vàng)

**Ví dụ:**
```
💵 Yêu cầu cấp kinh phí mới
   Có yêu cầu cấp kinh phí mới: "Tổ chức workshop" 
   từ CLB ABC. Số tiền: 5.000.000 VNĐ.
   🕐 5 phút trước • Nguyễn Văn A
```

---

#### 💰 B. **GIAO DỊCH QUỸ CHỜ DUYỆT** (Quan trọng)
**Khi nào:** Leader/Treasurer tạo giao dịch quỹ lớn (có thể set threshold)

**Thông tin:**
- 📝 Title: "Giao dịch quỹ mới (thu/chi)"
- 💰 Số tiền: XXX VNĐ
- 📊 Loại: Thu/Chi
- 🏢 CLB: {club_name}
- 📝 Nội dung: {title}

**Icon:** 💰 `fa-wallet` (màu xanh)

**Ví dụ:**
```
💰 Giao dịch quỹ mới (chi)
   Có giao dịch quỹ mới: "Mua thiết bị" 
   - 3.000.000 VNĐ từ CLB XYZ. Đang chờ duyệt.
   🕐 10 phút trước • CLB XYZ
```

---

#### 💰 C. **NỘP QUỸ TỪ THÀNH VIÊN** (Thông tin)
**Khi nào:** Thành viên nộp quỹ qua QR Code

**Thông tin:**
- 📝 Title: "Yêu cầu nộp quỹ mới từ CLB {club_name}"
- 💰 Số tiền: XXX VNĐ
- 👤 Người nộp: {user_name}
- 🏢 CLB: {club_name}
- 💳 Phương thức: Chuyển khoản/Tiền mặt

**Icon:** 💰 `fa-wallet` (màu xanh)

**Ví dụ:**
```
💰 Yêu cầu nộp quỹ mới từ CLB ABC
   Nguyễn Văn B từ CLB ABC đã nộp quỹ 
   số tiền 100.000 VNĐ. Đang chờ xác nhận.
   🕐 15 phút trước • Nguyễn Văn B
```

---

### ❌ CÁC THÔNG BÁO ADMIN KHÔNG NHẬN:

- ❌ ~~Yêu cầu tham gia CLB~~ (Leader/VP duyệt)
- ❌ ~~Bài viết mới từ CLB~~ (Không cần thiết)
- ❌ ~~Sự kiện mới~~ (Không cần thiết)
- ❌ ~~Giao dịch quỹ nhỏ~~ (Leader/Treasurer tự duyệt)

---

## 👑 2. LEADER (TRƯỞNG CLB)

### ✅ CÁC THÔNG BÁO LEADER NHẬN:

#### 👥 A. **YÊU CẦU THAM GIA CLB MỚI**
**Khi nào:** Sinh viên gửi yêu cầu tham gia CLB

**Thông tin:**
- 📝 Title: "Yêu cầu tham gia CLB mới"
- 👤 Người gửi: {user_name}
- 🆔 MSSV: {student_id}
- 📧 Email: {email}

**Icon:** 👥 `fa-user-plus` (màu xanh)

**Ví dụ:**
```
👥 Yêu cầu tham gia CLB mới
   Nguyễn Văn C đã gửi yêu cầu tham gia CLB của bạn.
   Vui lòng xem xét và duyệt đơn.
   🕐 5 phút trước • Nguyễn Văn C
```

---

#### 💰 B. **NỘP QUỸ TỪ THÀNH VIÊN**
**Khi nào:** Thành viên CLB nộp quỹ

**Thông tin:**
- 📝 Title: "Yêu cầu nộp quỹ mới"
- 👤 Người nộp: {user_name}
- 💰 Số tiền: XXX VNĐ
- 💳 Phương thức: {payment_method}
- 📸 Minh chứng: Có/Không

**Icon:** 💰 `fa-wallet` (màu xanh)

**Ví dụ:**
```
💰 Yêu cầu nộp quỹ mới
   Nguyễn Văn D đã nộp quỹ số tiền 50.000 VNĐ 
   qua Chuyển khoản ngân hàng. 
   Vui lòng kiểm tra và duyệt.
   🕐 10 phút trước • Nguyễn Văn D
```

---

#### 💰 C. **GIAO DỊCH QUỸ TỪ OFFICER/MEMBER**
**Khi nào:** Officer tạo giao dịch quỹ (cần Leader duyệt)

**Thông tin:**
- 📝 Title: "Giao dịch quỹ mới (thu/chi)"
- 👤 Người tạo: {user_name}
- 💰 Số tiền: XXX VNĐ
- 📊 Loại: Thu/Chi
- 📝 Nội dung: {description}

**Icon:** 💰 `fa-wallet` (màu xanh)

**Ví dụ:**
```
💰 Giao dịch quỹ mới (chi)
   Có giao dịch quỹ mới: "Mua văn phòng phẩm" 
   - 200.000 VNĐ trong CLB của bạn. Đang chờ duyệt.
   🕐 30 phút trước • Nguyễn Văn E
```

---

#### 💵 D. **YÊU CẦU KINH PHÍ ĐÃ DUYỆT/TỪ CHỐI**
**Khi nào:** Admin duyệt/từ chối yêu cầu kinh phí của CLB

**Thông tin:**
- ✅ Title: "Yêu cầu cấp kinh phí đã được duyệt"
- ❌ Title: "Yêu cầu cấp kinh phí đã bị từ chối"
- 💰 Số tiền: XXX VNĐ
- 📝 Yêu cầu: {title}
- 📋 Ghi chú: {admin_note}

**Icon:** ✅ `fa-check-circle` hoặc ❌ `fa-times-circle`

**Ví dụ:**
```
✅ Yêu cầu cấp kinh phí đã được duyệt
   Yêu cầu cấp kinh phí "Tổ chức workshop" 
   của bạn đã được duyệt. 
   Số tiền được duyệt: 5.000.000 VNĐ.
   🕐 1 giờ trước • Admin
```

---

#### 📅 E. **SỰ KIỆN MỚI/THAY ĐỔI** (Nếu cần)
**Khi nào:** Có sự kiện mới trong CLB hoặc sự kiện thay đổi

---

## 👥 3. VICE PRESIDENT (PHÓ CLB)

### ✅ NHẬN GIỐNG LEADER:

#### 👥 A. **YÊU CẦU THAM GIA CLB MỚI**
- Có quyền duyệt thành viên
- Nhận thông báo giống Leader

#### 📅 B. **SỰ KIỆN/HOẠT ĐỘNG CLB**
- Thông báo hoạt động quan trọng

#### 💬 C. **THÔNG BÁO TỪ LEADER**
- Thông báo nội bộ CLB

---

## 💰 4. TREASURER (THỦ QUỸ)

### ✅ CÁC THÔNG BÁO TREASURER NHẬN:

#### 💰 A. **NỘP QUỸ TỪ THÀNH VIÊN** (Quan trọng nhất)
**Khi nào:** Thành viên nộp quỹ

**Thông tin:**
- 📝 Title: "Yêu cầu nộp quỹ mới"
- 👤 Người nộp: {user_name}
- 💰 Số tiền: XXX VNĐ
- 💳 Phương thức: {payment_method}
- 📸 Minh chứng: Có/Không

**Icon:** 💰 `fa-wallet` (màu xanh)

**Ví dụ:**
```
💰 Yêu cầu nộp quỹ mới
   Nguyễn Văn F đã nộp quỹ số tiền 100.000 VNĐ 
   qua VietQR. Vui lòng kiểm tra và xác nhận.
   🕐 5 phút trước • Nguyễn Văn F
```

---

#### 💰 B. **GIAO DỊCH QUỸ MỚI**
**Khi nào:** Có giao dịch quỹ cần duyệt

**Thông tin:**
- 📝 Title: "Giao dịch quỹ mới (thu/chi)"
- 👤 Người tạo: {user_name}
- 💰 Số tiền: XXX VNĐ
- 📊 Loại: Thu/Chi

**Icon:** 💰 `fa-wallet` (màu xanh)

---

#### 📊 C. **BÁO CÁO QUỸ** (Nếu có)
**Khi nào:** Định kỳ hoặc khi có yêu cầu báo cáo

---

## 👤 5. MEMBER (THÀNH VIÊN BÌNH THƯỜNG)

### ✅ CÁC THÔNG BÁO MEMBER NHẬN:

#### ✅ A. **ĐƠN THAM GIA CLB ĐÃ DUYỆT**
**Khi nào:** Leader/VP duyệt đơn tham gia

**Thông tin:**
- 📝 Title: "Đơn tham gia CLB đã được duyệt"
- 🏢 CLB: {club_name}
- 👑 Người duyệt: Leader/VP

**Icon:** ✅ `fa-check-circle` (màu xanh)

**Ví dụ:**
```
✅ Đơn tham gia CLB đã được duyệt
   Đơn tham gia CLB "ABC" của bạn đã được duyệt 
   bởi ban quản trị CLB. 
   Chúc mừng bạn đã trở thành thành viên của CLB!
   🕐 30 phút trước • Leader CLB ABC
```

---

#### ❌ B. **ĐƠN THAM GIA CLB BỊ TỪ CHỐI**
**Khi nào:** Leader/VP từ chối đơn tham gia

**Thông tin:**
- 📝 Title: "Đơn tham gia CLB đã bị từ chối"
- 🏢 CLB: {club_name}
- 📋 Lý do: {reason}

**Icon:** ❌ `fa-times-circle` (màu đỏ)

---

#### 💰 C. **GIAO DỊCH QUỸ ĐÃ DUYỆT/TỪ CHỐI**
**Khi nào:** Leader/Treasurer duyệt/từ chối giao dịch quỹ member tạo

**Thông tin:**
- ✅ Title: "Giao dịch quỹ (thu/chi) đã được duyệt"
- ❌ Title: "Giao dịch quỹ đã bị từ chối"
- 💰 Số tiền: XXX VNĐ
- 📝 Giao dịch: {title}

**Icon:** ✅ `fa-check-circle` hoặc ❌ `fa-times-circle`

**Ví dụ:**
```
✅ Giao dịch quỹ (thu) đã được duyệt
   Giao dịch quỹ "Nộp quỹ tháng 1" của bạn 
   đã được duyệt bởi Thủ quỹ CLB ABC. 
   Số tiền: 50.000 VNĐ.
   🕐 1 giờ trước • Thủ quỹ CLB ABC
```

---

#### 📰 D. **BÀI VIẾT MỚI TỪ CLB**
**Khi nào:** CLB đăng bài viết/thông báo mới

**Thông tin:**
- 📝 Title: "Bài viết mới từ CLB {club_name}"
- 📰 Tiêu đề bài viết: {post_title}
- 👤 Người đăng: {user_name}

**Icon:** 📰 `fa-newspaper` (màu xám)

**Ví dụ:**
```
📰 Bài viết mới từ CLB ABC
   CLB ABC vừa đăng bài viết mới: 
   "Thông báo tổ chức sự kiện Workshop 2024"
   🕐 2 giờ trước • Admin CLB ABC
```

---

#### 📅 E. **SỰ KIỆN MỚI/THAY ĐỔI**
**Khi nào:** CLB tạo sự kiện mới hoặc sự kiện có thay đổi

**Thông tin:**
- 📝 Title: "Sự kiện mới từ CLB {club_name}"
- 📅 Tên sự kiện: {event_name}
- 📍 Địa điểm: {location}
- 📆 Thời gian: {date_time}

**Icon:** 📅 `fa-calendar` (màu vàng)

**Ví dụ:**
```
📅 Sự kiện mới từ CLB ABC
   CLB ABC vừa tạo sự kiện mới: "Workshop AI 2024"
   Thời gian: 15/01/2026, 14:00
   Địa điểm: Phòng A101
   🕐 3 giờ trước • CLB ABC
```

---

#### 🎉 F. **ĐĂNG KÝ SỰ KIỆN THÀNH CÔNG**
**Khi nào:** Đăng ký tham gia sự kiện thành công

---

#### 📢 G. **THÔNG BÁO CHUNG TỪ CLB**
**Khi nào:** CLB có thông báo quan trọng cho tất cả thành viên

---

## 📊 BẢNG TỔNG HỢP

| Loại Thông báo | Admin | Leader | VP | Treasurer | Member |
|----------------|-------|--------|----|-----------| -------|
| 💵 Yêu cầu cấp kinh phí | ✅ | ✅ (kết quả) | ✅ (kết quả) | ❌ | ❌ |
| 💰 Giao dịch quỹ lớn | ✅ | ✅ | ❌ | ✅ | ❌ |
| 💰 Nộp quỹ từ member | ✅ (info) | ✅ | ❌ | ✅ | ✅ (kết quả) |
| 👥 Yêu cầu tham gia CLB | ❌ | ✅ | ✅ | ❌ | ✅ (kết quả) |
| 📰 Bài viết mới | ❌ | ❌ | ❌ | ❌ | ✅ |
| 📅 Sự kiện mới | ❌ | ✅ | ✅ | ❌ | ✅ |
| 🎉 Đăng ký sự kiện | ❌ | ✅ | ✅ | ❌ | ✅ |

**Giải thích:**
- ✅ = Nhận thông báo
- ❌ = KHÔNG nhận
- ✅ (kết quả) = Chỉ nhận kết quả (duyệt/từ chối)
- ✅ (info) = Nhận để theo dõi, không cần action

---

## 🔔 ƯU TIÊN THÔNG BÁO

### 🔴 **MỨC ĐỘ CAO (Urgent)**
1. 💵 Yêu cầu cấp kinh phí mới (Admin)
2. 💰 Nộp quỹ từ member (Treasurer, Leader)
3. 👥 Yêu cầu tham gia CLB (Leader, VP)

### 🟡 **MỨC ĐỘ TRUNG BÌNH (Normal)**
4. 💰 Giao dịch quỹ chờ duyệt (Admin, Treasurer)
5. ✅ Kết quả duyệt (Member)
6. 📅 Sự kiện mới (All members)

### 🟢 **MỨC ĐỘ THẤP (Info)**
7. 📰 Bài viết mới (Members)
8. 📢 Thông báo chung (Members)

---

## 💡 KHUYẾN NGHỊ TRIỂN KHAI

### 1. **Priority Level trong Database**
Thêm column `priority` vào bảng `notifications`:
```sql
ALTER TABLE notifications ADD COLUMN priority ENUM('high', 'normal', 'low') DEFAULT 'normal';
```

### 2. **Filter theo Role**
Update NotificationService để tự động filter theo role:
```php
NotificationService::sendToClubLeaders(); // Leader + VP
NotificationService::sendToTreasurer(); // Chỉ Treasurer
NotificationService::sendToMembers(); // Tất cả members
```

### 3. **Badge phân màu theo Priority**
- 🔴 High: Badge đỏ
- 🟡 Normal: Badge vàng
- 🟢 Low: Badge xanh

---

**📅 Ngày tạo:** 9/1/2026  
**✅ Status:** Đề xuất phân loại  
**📝 Next step:** Implement theo phân loại này

