# HƯỚNG DẪN SỬ DỤNG TÍNH NĂNG QUYẾT TOÁN KINH PHÍ

## 📋 TỔNG QUAN

Hệ thống quản lý quỹ có **quy trình 4 bước**:
1. **Yêu cầu** - CLB tạo yêu cầu cấp kinh phí
2. **Duyệt** - Admin duyệt/từ chối yêu cầu
3. **Quyết toán** - CLB nộp hóa đơn và báo cáo chi tiêu thực tế
4. **Hoàn tất** - Hệ thống tự động cập nhật quỹ

## 🎯 TRƯỜNG HỢP XỬ LÝ TIỀN THỪA/THIẾU

### Khi quyết toán xong, hệ thống sẽ tự động xử lý:

#### ✅ **TRƯỜNG HỢP 1: Có tiền thừa**
```
Ví dụ: Duyệt 5.000.000 VNĐ, chi thực tế 3.500.000 VNĐ
→ Tiền thừa: 1.500.000 VNĐ

Hệ thống tự động:
- Tạo giao dịch CHI: 3.500.000 VNĐ (chi thực tế)
- Tạo giao dịch THU: 1.500.000 VNĐ (hoàn tiền thừa)
→ Quỹ CLB được hoàn lại tiền thừa
```

#### ⚠️ **TRƯỜNG HỢP 2: Có tiền thiếu**
```
Ví dụ: Duyệt 5.000.000 VNĐ, chi thực tế 6.500.000 VNĐ
→ Tiền thiếu: 1.500.000 VNĐ

Hệ thống:
- Tạo giao dịch CHI: 6.500.000 VNĐ (chi thực tế)
- Hiển thị cảnh báo trên giao diện
- CLB phải tự giải trình
```

#### ✅ **TRƯỜNG HỢP 3: Khớp số tiền**
```
Ví dụ: Duyệt 5.000.000 VNĐ, chi thực tế 5.000.000 VNĐ
→ Khớp hoàn toàn

Hệ thống:
- Tạo giao dịch CHI: 5.000.000 VNĐ
- Không có giao dịch hoàn tiền
```

## 📍 VỊ TRÍ CÁC CHỨC NĂNG

### 1. **Yêu cầu cấp kinh phí**
```
URL: http://localhost:8000/admin/fund-requests
Chức năng: 
- Xem danh sách yêu cầu cấp kinh phí
- Duyệt/từ chối yêu cầu
- Sau khi duyệt → tự động chuyển sang "Chờ quyết toán"
```

### 2. **Quyết toán kinh phí**
```
URL: http://localhost:8000/admin/fund-settlements
Chức năng:
- Xem danh sách yêu cầu "Chờ quyết toán"
- Thực hiện quyết toán
- Xem lịch sử "Yêu cầu đã quyết toán"
```

### 3. **Xem chi tiết quyết toán**
```
Trong danh sách "Yêu cầu đã quyết toán"
→ Nhấn "Xem chi tiết"
→ Xem thông tin chi tiết: số tiền duyệt, số tiền thực tế, hóa đơn, ghi chú
```

## 🔍 KIỂM TRA TÍNH NĂNG

### Bước 1: Vào trang quyết toán
```
http://localhost:8000/admin/fund-settlements
```

### Bước 2: Chọn yêu cầu cần quyết toán
```
- Nhấn nút "Quyết toán" bên cạnh yêu cầu
- Mở form quyết toán
```

### Bước 3: Nhập số tiền thực tế
```
- Nhập số tiền thực tế đã chi
- Hệ thống sẽ tự động hiển thị:
  - Tiền thừa (màu xanh dương)
  - Tiền thiếu (màu vàng)
  - Khớp (màu xanh lá)
```

### Bước 4: Upload hóa đơn
```
- Upload hóa đơn/chứng từ
- Bắt buộc nếu số tiền ≥ 1 triệu VNĐ
```

### Bước 5: Ghi chú
```
- Nhập ghi chú về việc chi tiêu
- Mô tả chi tiết các khoản đã sử dụng
```

### Bước 6: Hoàn tất
```
- Nhấn "Hoàn tất quyết toán"
- Hệ thống tự động:
  ✅ Cập nhật trạng thái: settlement_status = "settled"
  ✅ Tạo giao dịch chi tiêu
  ✅ Hoàn tiền thừa (nếu có)
  ✅ Cập nhật số dư quỹ CLB
```

### Bước 7: Xem kết quả
```
- Quay lại danh sách quyết toán
- Yêu cầu đã quyết toán xuất hiện ở phần "Yêu cầu đã quyết toán"
- Nhấn "Xem chi tiết" để kiểm tra
```

## 📊 XEM LỊCH SỬ QUYẾT TOÁN

### Cách xem:
1. Vào `http://localhost:8000/admin/fund-settlements`
2. Cuộn xuống phần **"Yêu cầu đã quyết toán"**
3. Xem danh sách tất cả yêu cầu đã được quyết toán
4. Có thể:
   - Xem số tiền duyệt vs số tiền thực tế
   - Xem ngày quyết toán
   - Xem người quyết toán
   - Xem chi tiết đầy đủ

## ❗ LƯU Ý

1. **Yêu cầu phải được duyệt** trước khi có thể quyết toán
2. **Hóa đơn bắt buộc** nếu số tiền ≥ 1 triệu VNĐ
3. **Số tiền thực tế** có thể lớn hơn số tiền duyệt (cần giải trình)
4. **Hệ thống tự động** xử lý việc hoàn tiền thừa
5. **Tất cả giao dịch** đều được lưu trong lịch sử quỹ

## 🔧 XỬ LÝ SỰ CỐ

### Không thấy nút "Quyết toán"?
→ Yêu cầu chưa được duyệt, cần duyệt trước

### Không thấy phần "Yêu cầu đã quyết toán"?
→ Hard refresh trình duyệt (Ctrl+F5)

### Không hiển thị tiền thừa/thiếu?
→ Kiểm tra JavaScript console (F12) xem có lỗi không

## 📞 HỖ TRỢ

Nếu còn vấn đề, vui lòng:
1. Mở Developer Tools (F12)
2. Xem tab "Console" để tìm lỗi
3. Chụp màn hình và gửi báo lỗi
