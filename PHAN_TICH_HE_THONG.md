# PHẦN 3. PHÂN TÍCH HỆ THỐNG

## 3.1 Phân tích hiện trạng (SWOT)

### Điểm mạnh (Strengths)
- **Hệ thống phân quyền linh hoạt**: Phân quyền theo vai trò (Admin, Leader, Vice President, Officer, Member) và theo từng CLB
- **Giao diện hiện đại**: Sử dụng Bootstrap 5, responsive design, hỗ trợ mobile
- **Quản lý đa dạng**: Quản lý CLB, thành viên, bài viết, sự kiện, quỹ, tài nguyên
- **Hệ thống thông báo**: Thông báo real-time cho admin và thành viên
- **Bảo mật**: Session-based authentication, CSRF protection, input validation
- **Tích hợp đầy đủ**: Quản lý từ đăng ký CLB đến quản lý tài chính

### Điểm yếu (Weaknesses)
- **Phụ thuộc database**: Cần MySQL server chạy liên tục, dễ lỗi khi database không kết nối
- **Chưa có API**: Chưa có RESTful API cho mobile app
- **Chưa có real-time**: Thông báo chưa có WebSocket, phải refresh để xem mới
- **Chưa có backup tự động**: Cần backup thủ công database
- **Performance**: Chưa có caching, có thể chậm khi dữ liệu lớn

### Cơ hội (Opportunities)
- **Mở rộng mobile app**: Có thể phát triển ứng dụng mobile với API
- **Tích hợp thanh toán**: Có thể tích hợp cổng thanh toán cho quỹ CLB
- **Tích hợp email**: Gửi email thông báo thay vì chỉ hiển thị trong hệ thống
- **Phân tích dữ liệu**: Thêm dashboard thống kê chi tiết, báo cáo xuất Excel/PDF
- **Tích hợp mạng xã hội**: Share bài viết, sự kiện lên Facebook, Zalo

### Thách thức (Threats)
- **Bảo mật dữ liệu**: Nguy cơ lộ thông tin cá nhân, cần mã hóa password, bảo vệ session
- **Scalability**: Khi số lượng CLB và thành viên tăng, cần tối ưu database
- **Maintenance**: Cần bảo trì thường xuyên, cập nhật security patches
- **User adoption**: Cần training cho admin và thành viên sử dụng hệ thống
- **Competition**: Có thể có hệ thống khác cạnh tranh

## 3.2 Danh sách tác nhân (Actor)

*<<Tham khảo phần đối tượng sử dụng ở trên để liệt kê danh sách actor (đối tác động đến hệ thống: con người, hệ thống khác như ngân hàng, cổng thanh toán...)>>*

### Tác nhân chính (Primary Actors)

| STT | Tên Actor | Nhiệm vụ / Mô tả |
|-----|----------|------------------|
| 1 | **Admin (Quản trị viên)** | Quản lý toàn hệ thống, duyệt/từ chối CLB, quản lý người dùng, xem thống kê tổng quan, quản lý quỹ hệ thống |
| 2 | **Sinh viên (Student)** | Đăng ký tài khoản, tham gia CLB, xem bài viết/sự kiện, đăng bài viết, bình luận, đăng ký sự kiện |
| 3 | **Trưởng CLB (Leader)** | Quản lý CLB (chỉnh sửa thông tin, logo), quản lý thành viên (duyệt đơn, phân quyền, xóa), tạo sự kiện, đăng thông báo, quản lý quỹ CLB, xem báo cáo |
| 4 | **Phó CLB (Vice President)** | Hỗ trợ quản lý thành viên, tạo sự kiện, đăng thông báo, quản lý quỹ (hạn chế), xem báo cáo |
| 5 | **Cán sự (Officer)** | Tạo sự kiện, đăng thông báo, upload tài nguyên, xem báo cáo |
| 6 | **Thành viên (Member)** | Xem thông tin CLB, xem bài viết/sự kiện, đăng ký sự kiện, bình luận, xem báo cáo cơ bản |

### Tác nhân hệ thống (System Actors)

| STT | Tên Actor | Nhiệm vụ / Mô tả |
|-----|----------|------------------|
| 7 | **Hệ thống thông báo** | Tự động gửi thông báo khi có bài viết mới, CLB được giải tán, sự kiện mới được tạo |
| 8 | **Hệ thống database** | Lưu trữ và quản lý dữ liệu (người dùng, CLB, bài viết, sự kiện, quỹ) |
| 9 | **Hệ thống file storage** | Lưu trữ file upload (ảnh, tài liệu, chứng từ) |

### Mô tả chi tiết Actor

#### 1. Admin (Quản trị viên)
- **Vai trò:** Quản lý toàn bộ hệ thống UniClubs
- **Quyền hạn:** 
  - Quản lý tất cả CLB (duyệt, từ chối, giải tán)
  - Quản lý người dùng (xem, xóa, khôi phục)
  - Quản lý bài viết, sự kiện, quỹ
  - Xem thống kê tổng quan
  - Quản lý phân quyền hệ thống
  - Quản lý thùng rác

#### 2. Sinh viên (Student)
- **Vai trò:** Người dùng chính của hệ thống
- **Quyền hạn:**
  - Đăng ký tài khoản với email trường (.edu.vn)
  - Xem danh sách CLB và tìm kiếm
  - Gửi yêu cầu tham gia CLB
  - Xem bài viết, sự kiện
  - Tạo bài viết trong CLB đã tham gia
  - Bình luận bài viết
  - Đăng ký tham gia sự kiện
  - Xem thông báo

#### 3. Trưởng CLB (Leader)
- **Vai trò:** Quản lý một CLB cụ thể
- **Quyền hạn:**
  - Chỉnh sửa thông tin CLB (tên, mô tả, logo)
  - Duyệt/từ chối yêu cầu tham gia
  - Phân quyền cho thành viên
  - Xóa thành viên
  - Tạo, chỉnh sửa, xóa sự kiện
  - Tạo, chỉnh sửa, xóa bài viết
  - Quản lý quỹ CLB (tạo yêu cầu, giao dịch)
  - Upload tài nguyên
  - Xem báo cáo chi tiết CLB

#### 4. Phó CLB (Vice President)
- **Vai trò:** Hỗ trợ Trưởng CLB quản lý CLB
- **Quyền hạn:**
  - Hỗ trợ quản lý thành viên
  - Tạo, chỉnh sửa sự kiện
  - Đăng thông báo
  - Quản lý quỹ (hạn chế)
  - Xem báo cáo

#### 5. Cán sự (Officer)
- **Vai trò:** Thực hiện các nhiệm vụ trong CLB
- **Quyền hạn:**
  - Tạo sự kiện
  - Đăng thông báo
  - Upload tài nguyên
  - Xem báo cáo

#### 6. Thành viên (Member)
- **Vai trò:** Thành viên cơ bản của CLB
- **Quyền hạn:**
  - Xem thông tin CLB
  - Xem bài viết, sự kiện
  - Đăng ký sự kiện
  - Bình luận bài viết
  - Xem báo cáo cơ bản

## 3.3 Danh sách các Use Case

*<<Liệt kê tên các use case, sử dụng động từ để đặt tên>>*

### A. Nhóm Use Case: Xác thực và Quản lý Người dùng

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC01 | Đăng ký tài khoản | Sinh viên đăng ký tài khoản mới với email trường |
| UC02 | Đăng nhập | Người dùng đăng nhập vào hệ thống |
| UC03 | Đăng xuất | Người dùng đăng xuất khỏi hệ thống |
| UC04 | Xem hồ sơ cá nhân | Xem thông tin cá nhân của mình |
| UC05 | Cập nhật hồ sơ cá nhân | Chỉnh sửa thông tin cá nhân |
| UC06 | Đổi mật khẩu | Thay đổi mật khẩu đăng nhập |
| UC07 | Upload ảnh đại diện | Tải lên và cập nhật ảnh đại diện |

### B. Nhóm Use Case: Quản lý Câu lạc bộ

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC08 | Tạo yêu cầu thành lập CLB | Sinh viên gửi yêu cầu thành lập CLB mới |
| UC09 | Duyệt câu lạc bộ | Admin duyệt yêu cầu thành lập CLB |
| UC10 | Từ chối câu lạc bộ | Admin từ chối yêu cầu thành lập CLB |
| UC11 | Xem danh sách câu lạc bộ | Xem tất cả CLB trong hệ thống |
| UC12 | Xem chi tiết câu lạc bộ | Xem thông tin chi tiết của một CLB |
| UC13 | Chỉnh sửa thông tin câu lạc bộ | Leader chỉnh sửa thông tin CLB |
| UC14 | Giải tán câu lạc bộ | Admin giải tán một CLB |
| UC15 | Tìm kiếm câu lạc bộ | Tìm kiếm CLB theo tên, lĩnh vực |
| UC16 | Upload logo CLB | Leader tải lên logo cho CLB |

### C. Nhóm Use Case: Quản lý Thành viên

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC17 | Gửi yêu cầu tham gia CLB | Sinh viên gửi yêu cầu tham gia CLB |
| UC18 | Duyệt yêu cầu tham gia | Leader duyệt yêu cầu tham gia |
| UC19 | Từ chối yêu cầu tham gia | Leader từ chối yêu cầu tham gia |
| UC20 | Hủy yêu cầu tham gia | Sinh viên hủy yêu cầu đã gửi |
| UC21 | Rời khỏi CLB | Thành viên rời khỏi CLB |
| UC22 | Xem danh sách thành viên | Xem danh sách thành viên của CLB |
| UC23 | Phân quyền thành viên | Leader phân quyền cho thành viên |
| UC24 | Xóa thành viên | Leader xóa thành viên khỏi CLB |
| UC25 | Chuyển giao vai trò | Leader chuyển giao vai trò cho thành viên khác |

### D. Nhóm Use Case: Quản lý Bài viết

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC26 | Tạo bài viết | Tạo bài viết mới trong CLB |
| UC27 | Chỉnh sửa bài viết | Chỉnh sửa nội dung bài viết |
| UC28 | Xóa bài viết | Xóa bài viết (chuyển vào thùng rác) |
| UC29 | Xem danh sách bài viết | Xem danh sách bài viết của CLB |
| UC30 | Xem chi tiết bài viết | Xem nội dung chi tiết bài viết |
| UC31 | Tìm kiếm bài viết | Tìm kiếm bài viết theo từ khóa |
| UC32 | Upload ảnh bài viết | Tải lên ảnh cho bài viết |
| UC33 | Quản lý bài viết | Admin quản lý tất cả bài viết |

### E. Nhóm Use Case: Quản lý Bình luận

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC34 | Bình luận bài viết | Thêm bình luận vào bài viết |
| UC35 | Chỉnh sửa bình luận | Chỉnh sửa nội dung bình luận |
| UC36 | Xóa bình luận | Xóa bình luận của mình |
| UC37 | Xem bình luận | Xem danh sách bình luận của bài viết |

### F. Nhóm Use Case: Quản lý Sự kiện

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC38 | Tạo sự kiện | Leader/Officer tạo sự kiện mới |
| UC39 | Chỉnh sửa sự kiện | Chỉnh sửa thông tin sự kiện |
| UC40 | Xóa sự kiện | Xóa sự kiện |
| UC41 | Xem danh sách sự kiện | Xem tất cả sự kiện |
| UC42 | Xem chi tiết sự kiện | Xem thông tin chi tiết sự kiện |
| UC43 | Đăng ký sự kiện | Sinh viên đăng ký tham gia sự kiện |
| UC44 | Hủy đăng ký sự kiện | Hủy đăng ký tham gia sự kiện |
| UC45 | Duyệt sự kiện | Admin duyệt sự kiện |
| UC46 | Từ chối sự kiện | Admin từ chối sự kiện |
| UC47 | Quản lý đăng ký sự kiện | Leader xem danh sách người đăng ký |

### G. Nhóm Use Case: Quản lý Quỹ CLB

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC48 | Tạo yêu cầu quỹ | Leader tạo yêu cầu sử dụng quỹ |
| UC49 | Duyệt yêu cầu quỹ | Admin duyệt yêu cầu quỹ |
| UC50 | Từ chối yêu cầu quỹ | Admin từ chối yêu cầu quỹ |
| UC51 | Tạo giao dịch quỹ | Leader tạo giao dịch thu/chi quỹ |
| UC52 | Duyệt giao dịch quỹ | Admin duyệt giao dịch quỹ |
| UC53 | Xem lịch sử giao dịch | Xem lịch sử các giao dịch quỹ |
| UC54 | Xem báo cáo tài chính | Xem báo cáo thu chi quỹ CLB |
| UC55 | Upload chứng từ | Tải lên chứng từ cho giao dịch |
| UC56 | Quyết toán quỹ | Admin quyết toán yêu cầu quỹ |

### H. Nhóm Use Case: Quản lý Tài nguyên

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC57 | Upload tài nguyên | Leader/Officer tải lên tài liệu |
| UC58 | Xem tài nguyên | Xem danh sách tài nguyên CLB |
| UC59 | Tải xuống tài nguyên | Tải xuống tài liệu từ CLB |
| UC60 | Xóa tài nguyên | Leader xóa tài nguyên |
| UC61 | Chỉnh sửa tài nguyên | Chỉnh sửa thông tin tài nguyên |

### I. Nhóm Use Case: Quản lý Thông báo

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC62 | Xem thông báo | Xem danh sách thông báo |
| UC63 | Đánh dấu đã đọc | Đánh dấu thông báo đã đọc |
| UC64 | Xóa thông báo | Xóa thông báo |
| UC65 | Lọc thông báo | Lọc thông báo theo trạng thái, người gửi, loại |
| UC66 | Tìm kiếm thông báo | Tìm kiếm thông báo theo từ khóa |
| UC67 | Xem chi tiết thông báo | Xem nội dung chi tiết thông báo |
| UC68 | Click xem bài viết từ thông báo | Chuyển đến trang bài viết từ thông báo |

### J. Nhóm Use Case: Quản lý Phân quyền

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC69 | Phân quyền cho thành viên | Leader phân quyền cho thành viên |
| UC70 | Xem danh sách quyền | Xem các quyền có trong hệ thống |
| UC71 | Cập nhật quyền | Cập nhật quyền của thành viên |
| UC72 | Xem quyền của thành viên | Xem quyền hiện tại của một thành viên |

### K. Nhóm Use Case: Dashboard và Báo cáo

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC73 | Xem dashboard admin | Admin xem tổng quan hệ thống |
| UC74 | Xem dashboard sinh viên | Sinh viên xem dashboard cá nhân |
| UC75 | Xem thống kê CLB | Xem thống kê hoạt động CLB |
| UC76 | Xuất báo cáo | Xuất báo cáo ra file Excel/PDF |
| UC77 | Xem thống kê người dùng | Admin xem thống kê người dùng |
| UC78 | Xem thống kê bài viết | Xem thống kê bài viết theo CLB |

### L. Nhóm Use Case: Quản lý Hệ thống (Admin)

| STT | Use Case | Mô tả ngắn |
|-----|----------|------------|
| UC79 | Quản lý người dùng | Admin quản lý tất cả người dùng |
| UC80 | Xóa người dùng | Admin xóa người dùng |
| UC81 | Khôi phục người dùng | Admin khôi phục người dùng đã xóa |
| UC82 | Quản lý thùng rác | Admin quản lý dữ liệu đã xóa |
| UC83 | Khôi phục dữ liệu | Khôi phục dữ liệu từ thùng rác |
| UC84 | Xóa vĩnh viễn | Xóa vĩnh viễn dữ liệu |
| UC85 | Tìm kiếm tổng hợp | Tìm kiếm người dùng, CLB, bài viết |
| UC86 | Cài đặt hệ thống | Cấu hình các thông số hệ thống |

### Tổng kết

**Tổng số Use Case: 86**

- **Nhóm A (Xác thực):** 7 use case
- **Nhóm B (Quản lý CLB):** 9 use case  
- **Nhóm C (Quản lý thành viên):** 9 use case
- **Nhóm D (Quản lý bài viết):** 8 use case
- **Nhóm E (Quản lý bình luận):** 4 use case
- **Nhóm F (Quản lý sự kiện):** 10 use case
- **Nhóm G (Quản lý quỹ):** 9 use case
- **Nhóm H (Quản lý tài nguyên):** 5 use case
- **Nhóm I (Quản lý thông báo):** 7 use case
- **Nhóm J (Quản lý phân quyền):** 4 use case
- **Nhóm K (Dashboard & Báo cáo):** 6 use case
- **Nhóm L (Quản lý hệ thống):** 8 use case

### Bảng ánh xạ Actor - Use Case chính

| Actor | Các Use Case chính |
|-------|-------------------|
| **Admin** | UC09, UC10, UC14, UC33, UC43, UC45, UC46, UC49, UC50, UC52, UC73, UC79-UC86 |
| **Sinh viên** | UC01, UC02, UC04, UC05, UC08, UC11, UC12, UC15, UC17, UC20, UC26, UC30, UC34, UC41, UC58, UC59, UC62, UC74 |
| **Trưởng CLB** | UC13, UC16, UC18, UC19, UC23, UC24, UC25, UC26, UC27, UC28, UC38, UC39, UC40, UC48, UC51, UC57, UC60, UC69, UC71, UC75 |
| **Phó CLB** | UC26, UC38, UC39, UC57, UC58 |
| **Cán sự** | UC26, UC38, UC57 |
| **Thành viên** | UC30, UC34, UC41, UC58, UC59, UC62 |

## 3.4 Mô hình hệ thống (Use Case Model)

*<<Vẽ sơ đồ use case, vd sử dụng công cụ draw.io hoặc các công cụ vẽ khác để vẽ. Vẽ sơ đồ tổng quát và sơ đồ chi tiết theo từng use case, không vẽ chi tiết theo tác nhân>>*

### Sơ đồ Use Case tổng quát

```
┌─────────────────────────────────────────────────────────────┐
│                    HỆ THỐNG UNICLUBS                        │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌─────▼─────┐        ┌─────▼─────┐
   │  Admin  │          │  Student  │        │  Leader   │
   └────┬────┘          └─────┬─────┘        └─────┬─────┘
        │                     │                     │
        ├─ Quản lý CLB        ├─ Đăng ký            ├─ Quản lý CLB
        ├─ Quản lý User       ├─ Tham gia CLB       ├─ Quản lý thành viên
        ├─ Duyệt CLB          ├─ Xem bài viết       ├─ Tạo sự kiện
        ├─ Quản lý quỹ        ├─ Đăng bài viết      ├─ Quản lý quỹ
        ├─ Xem thống kê       ├─ Đăng ký sự kiện    ├─ Phân quyền
        └─ Quản lý thông báo   └─ Xem thông báo      └─ Xem báo cáo
```

### Sơ đồ Use Case chi tiết - Quản lý CLB

```
┌─────────────────────────────────────────────────────────────┐
│              USE CASE: QUẢN LÝ CÂU LẠC BỘ                   │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌─────▼─────┐        ┌─────▼─────┐
   │  Admin  │          │  Student  │        │  Leader   │
   └────┬────┘          └─────┬─────┘        └─────┬─────┘
        │                     │                     │
        ├─ Tạo CLB            ├─ Xem CLB            ├─ Chỉnh sửa CLB
        ├─ Duyệt CLB          ├─ Tìm kiếm CLB       ├─ Quản lý thành viên
        ├─ Từ chối CLB        ├─ Gửi yêu cầu        ├─ Upload logo
        ├─ Giải tán CLB       └─ Tham gia CLB       └─ Xem thống kê
        └─ Xem danh sách CLB
```

### Sơ đồ Use Case chi tiết - Quản lý Bài viết

```
┌─────────────────────────────────────────────────────────────┐
│              USE CASE: QUẢN LÝ BÀI VIẾT                     │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌─────▼─────┐        ┌─────▼─────┐
   │  Admin  │          │  Student  │        │  Leader   │
   └────┬────┘          └─────┬─────┘        └─────┬─────┘
        │                     │                     │
        ├─ Xem tất cả bài viết ├─ Tạo bài viết       ├─ Tạo bài viết
        ├─ Xóa bài viết        ├─ Chỉnh sửa bài viết ├─ Chỉnh sửa bài viết
        ├─ Quản lý bài viết    ├─ Xóa bài viết       ├─ Xóa bài viết
        └─ Xem thống kê        ├─ Xem bài viết       └─ Duyệt bài viết
                               └─ Bình luận
```

### Sơ đồ Use Case chi tiết - Quản lý Sự kiện

```
┌─────────────────────────────────────────────────────────────┐
│              USE CASE: QUẢN LÝ SỰ KIỆN                      │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌─────▼─────┐        ┌─────▼─────┐
   │  Admin  │          │  Student  │        │  Leader   │
   └────┬────┘          └─────┬─────┘        └─────┬─────┘
        │                     │                     │
        ├─ Duyệt sự kiện      ├─ Xem sự kiện       ├─ Tạo sự kiện
        ├─ Từ chối sự kiện    ├─ Đăng ký sự kiện   ├─ Chỉnh sửa sự kiện
        ├─ Xem tất cả sự kiện ├─ Hủy đăng ký       ├─ Xóa sự kiện
        └─ Xem thống kê       └─ Xem lịch sự kiện  └─ Quản lý đăng ký
```

### Sơ đồ Use Case chi tiết - Quản lý Quỹ

```
┌─────────────────────────────────────────────────────────────┐
│              USE CASE: QUẢN LÝ QUỸ CLB                      │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌─────▼─────┐        ┌─────▼─────┐
   │  Admin  │          │  Member   │        │  Leader   │
   └────┬────┘          └─────┬─────┘        └─────┬─────┘
        │                     │                     │
        ├─ Duyệt yêu cầu quỹ  ├─ Xem lịch sử quỹ   ├─ Tạo yêu cầu quỹ
        ├─ Từ chối yêu cầu    ├─ Xem báo cáo       ├─ Tạo giao dịch
        ├─ Xem báo cáo tổng   └─ Xem số dư         ├─ Duyệt giao dịch
        └─ Quản lý quỹ hệ thống                     ├─ Upload chứng từ
                                                    └─ Xem báo cáo chi tiết
```

## 3.5 Mô tả Use Case

| STT | Use Case | Mô tả chung | Input | Output |
|-----|----------|-------------|-------|--------|
| 1 | Đăng ký | Sinh viên đăng ký tài khoản để sử dụng hệ thống. Chỉ cho phép email trường (.edu.vn) | Họ tên, email (.edu.vn), mật khẩu, số điện thoại, địa chỉ | Thông báo đăng ký thành công/thất bại, tự động đăng nhập nếu thành công |
| 2 | Đăng nhập | Người dùng và admin đăng nhập để sử dụng hệ thống | Email và mật khẩu | Chuyển hướng đến dashboard tương ứng (admin/student) nếu thành công |
| 3 | Đăng xuất | Người dùng đăng xuất khỏi hệ thống | Không | Xóa session, chuyển về trang đăng nhập |
| 4 | Tạo câu lạc bộ | Sinh viên tạo yêu cầu thành lập CLB mới | Tên CLB, mô tả, lĩnh vực, logo, quy chế | Yêu cầu được gửi đến admin để duyệt |
| 5 | Duyệt câu lạc bộ | Admin duyệt yêu cầu thành lập CLB | ID CLB, quyết định duyệt | CLB được kích hoạt, thông báo cho người tạo |
| 6 | Gửi yêu cầu tham gia CLB | Sinh viên gửi yêu cầu tham gia một CLB | ID CLB | Yêu cầu được gửi đến Leader để duyệt |
| 7 | Duyệt yêu cầu tham gia | Leader duyệt yêu cầu tham gia CLB | ID yêu cầu, quyết định | Thành viên được thêm vào CLB, thông báo cho người đăng ký |
| 8 | Tạo bài viết | Sinh viên/Leader tạo bài viết trong CLB | Tiêu đề, nội dung, ảnh đại diện, CLB, loại (bài viết/thông báo) | Bài viết được đăng, thông báo cho admin |
| 9 | Chỉnh sửa bài viết | Người tạo bài viết chỉnh sửa nội dung | ID bài viết, nội dung mới | Bài viết được cập nhật |
| 10 | Xóa bài viết | Người tạo hoặc admin xóa bài viết | ID bài viết | Bài viết bị xóa, chuyển vào thùng rác |
| 11 | Bình luận bài viết | Sinh viên bình luận trên bài viết | ID bài viết, nội dung bình luận | Bình luận được hiển thị dưới bài viết |
| 12 | Tạo sự kiện | Leader/Officer tạo sự kiện cho CLB | Tên sự kiện, mô tả, thời gian, địa điểm, ảnh | Sự kiện được tạo, chờ admin duyệt |
| 13 | Đăng ký sự kiện | Sinh viên đăng ký tham gia sự kiện | ID sự kiện | Đăng ký thành công, thông báo cho Leader |
| 14 | Tạo yêu cầu quỹ | Leader tạo yêu cầu sử dụng quỹ CLB | Số tiền, lý do, mô tả, chứng từ | Yêu cầu được gửi, chờ admin duyệt |
| 15 | Duyệt yêu cầu quỹ | Admin duyệt yêu cầu sử dụng quỹ | ID yêu cầu, quyết định | Quỹ được giải ngân hoặc từ chối |
| 16 | Tạo giao dịch quỹ | Leader tạo giao dịch thu/chi quỹ | Loại (thu/chi), số tiền, mô tả, chứng từ | Giao dịch được ghi nhận |
| 17 | Phân quyền thành viên | Leader phân quyền cho thành viên trong CLB | ID thành viên, danh sách quyền | Quyền được cập nhật, position tự động thay đổi |
| 18 | Upload tài nguyên | Leader/Officer upload tài liệu cho CLB | File, tên, mô tả, CLB | Tài nguyên được lưu, thành viên có thể tải xuống |
| 19 | Xem thông báo | Người dùng xem danh sách thông báo | Không | Danh sách thông báo, có thể lọc và tìm kiếm |
| 20 | Đánh dấu đã đọc | Người dùng đánh dấu thông báo đã đọc | ID thông báo | Thông báo được đánh dấu đã đọc |
| 21 | Xem dashboard | Admin/Sinh viên xem tổng quan hệ thống | Không | Thống kê, biểu đồ, thông tin tổng quan |
| 22 | Xem báo cáo tài chính | Leader/Admin xem báo cáo quỹ CLB | ID CLB, khoảng thời gian | Báo cáo thu chi, biểu đồ, tổng kết |

## 3.6 Ma trận phân quyền chức năng

| STT | Chức năng | Khách | Sinh viên | Thành viên | Cán sự | Phó CLB | Trưởng CLB | Admin |
|-----|-----------|-------|-----------|------------|--------|---------|------------|-------|
| 1 | Đăng ký | √ | - | - | - | - | - | - |
| 2 | Đăng nhập | √ | √ | √ | √ | √ | √ | √ |
| 3 | Xem danh sách CLB | √ | √ | √ | √ | √ | √ | √ |
| 4 | Xem chi tiết CLB | √ | √ | √ | √ | √ | √ | √ |
| 5 | Tìm kiếm CLB | √ | √ | √ | √ | √ | √ | √ |
| 6 | Tạo yêu cầu CLB | - | √ | √ | √ | √ | √ | - |
| 7 | Gửi yêu cầu tham gia CLB | - | √ | √ | √ | √ | - | - |
| 8 | Duyệt yêu cầu tham gia | - | - | - | - | √ | √ | √ |
| 9 | Rời khỏi CLB | - | √ | √ | √ | √ | - | - |
| 10 | Chỉnh sửa thông tin CLB | - | - | - | - | - | √ | √ |
| 11 | Upload logo CLB | - | - | - | - | - | √ | √ |
| 12 | Giải tán CLB | - | - | - | - | - | - | √ |
| 13 | Duyệt CLB | - | - | - | - | - | - | √ |
| 14 | Xem danh sách thành viên | - | - | √ | √ | √ | √ | √ |
| 15 | Phân quyền thành viên | - | - | - | - | - | √ | √ |
| 16 | Xóa thành viên | - | - | - | - | - | √ | √ |
| 17 | Chuyển giao vai trò | - | - | - | - | - | √ | - |
| 18 | Tạo bài viết | - | √ | √ | √ | √ | √ | √ |
| 19 | Chỉnh sửa bài viết | - | √* | √* | √* | √* | √* | √ |
| 20 | Xóa bài viết | - | √* | √* | √* | √* | √* | √ |
| 21 | Xem bài viết | √ | √ | √ | √ | √ | √ | √ |
| 22 | Bình luận bài viết | - | √ | √ | √ | √ | √ | √ |
| 23 | Xóa bình luận | - | √* | - | - | - | √ | √ |
| 24 | Tạo sự kiện | - | - | - | √ | √ | √ | √ |
| 25 | Chỉnh sửa sự kiện | - | - | - | √* | √* | √* | √ |
| 26 | Xóa sự kiện | - | - | - | √* | √* | √* | √ |
| 27 | Duyệt sự kiện | - | - | - | - | - | - | √ |
| 28 | Đăng ký sự kiện | - | √ | √ | √ | √ | √ | √ |
| 29 | Hủy đăng ký sự kiện | - | √* | √* | √* | √* | √* | √ |
| 30 | Tạo yêu cầu quỹ | - | - | - | - | - | √ | - |
| 31 | Duyệt yêu cầu quỹ | - | - | - | - | - | - | √ |
| 32 | Tạo giao dịch quỹ | - | - | - | - | - | √ | √ |
| 33 | Xem lịch sử quỹ | - | - | √ | √ | √ | √ | √ |
| 34 | Xem báo cáo tài chính | - | - | √ | √ | √ | √ | √ |
| 35 | Upload tài nguyên | - | - | - | √ | √ | √ | √ |
| 36 | Tải xuống tài nguyên | - | √ | √ | √ | √ | √ | √ |
| 37 | Xóa tài nguyên | - | - | - | - | - | √ | √ |
| 38 | Xem thông báo | - | √ | √ | √ | √ | √ | √ |
| 39 | Đánh dấu đã đọc | - | √ | √ | √ | √ | √ | √ |
| 40 | Xóa thông báo | - | - | - | - | - | - | √ |
| 41 | Quản lý người dùng | - | - | - | - | - | - | √ |
| 42 | Quản lý quyền | - | - | - | - | - | - | √ |
| 43 | Xem dashboard admin | - | - | - | - | - | - | √ |
| 44 | Xem thống kê tổng quan | - | - | - | - | - | - | √ |
| 45 | Quản lý thùng rác | - | - | - | - | - | - | √ |
| 46 | Khôi phục dữ liệu | - | - | - | - | - | - | √ |

*Chỉ được thực hiện với dữ liệu do chính người dùng tạo

## 3.7 Sơ đồ hoạt động

### Sơ đồ hoạt động - Quy trình tham gia CLB

```
[START] → [Sinh viên đăng nhập] → [Xem danh sách CLB]
    ↓
[Tìm kiếm CLB] → [Xem chi tiết CLB] → [Gửi yêu cầu tham gia]
    ↓
[Leader nhận thông báo] → [Xem yêu cầu] → {Quyết định}
    ↓                    ↓
[DUYỆT]              [TỪ CHỐI]
    ↓                    ↓
[Thêm thành viên]   [Thông báo từ chối]
    ↓
[Thông báo chấp nhận] → [Sinh viên trở thành thành viên] → [END]
```

### Sơ đồ hoạt động - Quy trình tạo và duyệt bài viết

```
[START] → [Sinh viên/Leader đăng nhập] → [Chọn CLB]
    ↓
[Tạo bài viết] → [Nhập tiêu đề, nội dung, upload ảnh]
    ↓
[Lưu bài viết] → [Bài viết được đăng]
    ↓
[Hệ thống tạo thông báo] → [Admin nhận thông báo]
    ↓
[Admin xem thông báo] → [Click vào nội dung] → [Xem bài viết]
    ↓
[END]
```

### Sơ đồ hoạt động - Quy trình quản lý quỹ CLB

```
[START] → [Leader đăng nhập] → [Vào quản lý quỹ]
    ↓
{Loại giao dịch}
    ↓                    ↓
[TẠO YÊC CẦU]      [TẠO GIAO DỊCH]
    ↓                    ↓
[Nhập thông tin]   [Nhập thu/chi]
    ↓                    ↓
[Upload chứng từ]  [Upload chứng từ]
    ↓                    ↓
[Gửi yêu cầu]      [Lưu giao dịch]
    ↓                    ↓
[Admin duyệt]      [Ghi nhận vào quỹ]
    ↓                    ↓
{Quyết định}       [Cập nhật số dư]
    ↓                    ↓
[DUYỆT/TỪ CHỐI]    [END]
    ↓
[Giải ngân/Thông báo] → [END]
```

### Sơ đồ hoạt động - Quy trình phân quyền thành viên

```
[START] → [Leader đăng nhập] → [Vào quản lý thành viên]
    ↓
[Chọn thành viên] → [Vào phân quyền]
    ↓
[Chọn quyền] → [Lưu phân quyền]
    ↓
[Hệ thống tính số quyền] → {Xác định position}
    ↓
{Position}
    ↓        ↓          ↓           ↓
[LEADER] [VICE_PRES] [OFFICER] [MEMBER]
    ↓        ↓          ↓           ↓
[Cập nhật position] → [Thông báo cho thành viên] → [END]
```

### Sơ đồ hoạt động - Quy trình đăng ký và đăng nhập

```
[START] → {Chưa có tài khoản?}
    ↓                    ↓
[CÓ]                  [KHÔNG]
    ↓                    ↓
[Đăng ký]          [Đăng nhập]
    ↓                    ↓
[Nhập thông tin]   [Nhập email/password]
    ↓                    ↓
[Kiểm tra email .edu.vn] [Xác thực]
    ↓                    ↓
{Hợp lệ?}           {Đúng?}
    ↓                    ↓
[CÓ]                  [CÓ]
    ↓                    ↓
[Tạo tài khoản]    [Tạo session]
    ↓                    ↓
[Tự động đăng nhập] [Lấy thông tin user]
    ↓                    ↓
[Lưu session]      [Lấy club roles]
    ↓                    ↓
[Chuyển đến dashboard] → [END]
```

---

**Lưu ý:** 
- Các sơ đồ trên có thể được vẽ chi tiết hơn bằng công cụ như draw.io, Lucidchart, hoặc PlantUML
- Nên bổ sung thêm các sơ đồ cho các use case quan trọng khác như quản lý sự kiện, quản lý tài nguyên
- Có thể tạo sơ đồ sequence diagram để mô tả chi tiết luồng tương tác giữa các actor

