# Hướng dẫn tính năng hủy sự kiện với lý do

## ✅ Đã hoàn thành

### 1. Cơ sở dữ liệu
- **Migration**: Thêm trường `cancellation_reason` (TEXT) và `cancelled_at` (TIMESTAMP) vào bảng `events`
- **Model**: Cập nhật `Event` model với các trường mới và casting phù hợp

### 2. Giao diện người dùng
- **Modal hủy sự kiện**: Thay thế confirm dialog bằng modal Bootstrap với form nhập lý do
- **Validation**: Form yêu cầu nhập lý do hủy (10-1000 ký tự)
- **UI/UX**: Giao diện đẹp mắt với cảnh báo rõ ràng

### 3. Backend xử lý
- **Controller**: Cập nhật `eventsCancel()` method với validation và xử lý lý do hủy
- **Security**: Kiểm tra quyền admin và trạng thái sự kiện trước khi hủy
- **Error handling**: Xử lý lỗi và validation đầy đủ

### 4. Hiển thị thông tin
- **Chi tiết sự kiện**: Hiển thị lý do hủy với thiết kế đặc biệt
- **Danh sách sự kiện**: Hiển thị lý do hủy ngắn gọn trong danh sách
- **Styling**: CSS đẹp mắt với màu sắc phù hợp cho thông tin hủy

## 🎯 Cách sử dụng

### Hủy sự kiện
1. **Truy cập** trang quản lý sự kiện (Events hoặc Kế hoạch)
2. **Tìm sự kiện** có trạng thái: Chờ duyệt, Đã duyệt, hoặc Đang diễn ra
3. **Click nút "Hủy"** (màu đỏ)
4. **Modal hiện ra** yêu cầu nhập lý do hủy
5. **Nhập lý do** (tối thiểu 10 ký tự, tối đa 1000 ký tự)
6. **Click "Xác nhận hủy sự kiện"**

### Xem lý do hủy
1. **Trang chi tiết sự kiện**: Lý do hủy hiển thị trong khung đặc biệt màu đỏ
2. **Danh sách sự kiện**: Lý do hủy hiển thị dưới tên sự kiện với icon cảnh báo
3. **Thông tin bổ sung**: Hiển thị thời gian hủy sự kiện

## 🔧 Cấu hình kỹ thuật

### Database Schema
```sql
ALTER TABLE events ADD COLUMN cancellation_reason TEXT NULL AFTER status;
ALTER TABLE events ADD COLUMN cancelled_at TIMESTAMP NULL AFTER cancellation_reason;
```

### Model Configuration
```php
protected $fillable = [
    // ... existing fields
    'cancellation_reason',
    'cancelled_at',
];

protected $casts = [
    'cancelled_at' => 'datetime',
];
```

### Validation Rules
```php
'cancellation_reason' => 'required|string|min:10|max:1000'
```

### Controller Logic
- Kiểm tra quyền admin
- Validate lý do hủy
- Kiểm tra trạng thái sự kiện có thể hủy
- Cập nhật database với lý do và thời gian hủy
- Redirect với thông báo thành công

## 🎨 Giao diện

### Modal hủy sự kiện
- **Header**: Màu đỏ với icon cảnh báo
- **Body**: Form textarea với validation
- **Footer**: Nút hủy bỏ và xác nhận
- **Cảnh báo**: Thông báo rõ ràng về hành động không thể hoàn tác

### Hiển thị lý do hủy
- **Thiết kế**: Khung màu đỏ với gradient đẹp mắt
- **Icon**: Biểu tượng cảnh báo
- **Typography**: Font chữ rõ ràng, dễ đọc
- **Responsive**: Tự động điều chỉnh trên mobile

## 🚀 Lợi ích

1. **Minh bạch**: Người dùng biết rõ lý do sự kiện bị hủy
2. **Trách nhiệm**: Admin phải giải thích lý do hủy
3. **Lưu trữ**: Thông tin hủy được lưu trữ vĩnh viễn
4. **UX tốt**: Giao diện thân thiện, dễ sử dụng
5. **Bảo mật**: Chỉ admin mới có thể hủy sự kiện

## 📝 Lưu ý

- **Không thể hoàn tác**: Một khi đã hủy, không thể khôi phục
- **Lý do bắt buộc**: Phải nhập lý do hủy để hoàn tất thao tác
- **Trạng thái**: Chỉ hủy được sự kiện ở trạng thái phù hợp
- **Hiển thị**: Lý do hủy hiển thị công khai cho tất cả người dùng
- **Lưu trữ**: Thông tin hủy được lưu trữ vĩnh viễn trong database

## 🔄 Cập nhật trong tương lai

Có thể mở rộng thêm:
- **Email thông báo**: Gửi email cho người đăng ký khi sự kiện bị hủy
- **Lịch sử**: Theo dõi lịch sử thay đổi trạng thái sự kiện
- **Phân loại lý do**: Dropdown với các lý do hủy phổ biến
- **Báo cáo**: Thống kê sự kiện bị hủy theo lý do

