# KỊCH BẢN KIỂM THỬ

## TỔNG QUAN

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | WEB QL Câu lạc bộ sinh viên |
| **Số trường hợp kiểm thử đạt (P)** | 72 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 72 |

---

## BẢNG TỔNG HỢP TẤT CẢ CÁC MODULE

| Modules code | Pass | Fail | Untested | N/A | Number of test |
|--------------|------|------|----------|-----|----------------|
| Đăng nhập/Đăng ký | 8 | 0 | 0 | 0 | 8 |
| Quản lý người dùng (Admin) | 7 | 0 | 0 | 0 | 7 |
| Quản lý câu lạc bộ (Admin) | 9 | 0 | 0 | 0 | 9 |
| Quản lý sự kiện (Admin) | 5 | 0 | 0 | 0 | 5 |
| Quản lý bài viết (Admin) | 5 | 0 | 0 | 0 | 5 |
| Quản lý quỹ (Admin) | 7 | 0 | 0 | 0 | 7 |
| Sinh viên tham gia CLB | 5 | 0 | 0 | 0 | 5 |
| Sinh viên đăng ký sự kiện | 5 | 0 | 0 | 0 | 5 |
| Sinh viên đăng bài viết | 3 | 0 | 0 | 0 | 3 |
| Dashboard và Thống kê | 3 | 0 | 0 | 0 | 3 |
| Bảo mật và Phân quyền | 5 | 0 | 0 | 0 | 5 |
| Quản lý tài nguyên CLB | 3 | 0 | 0 | 0 | 3 |
| Quản lý tin tức ký túc xá | 7 | 0 | 0 | 0 | 7 |
| **TỔNG CỘNG** | **72** | **0** | **0** | **0** | **72** |

---

## CHI TIẾT CÁC TEST CASE

### Chức năng : Đăng nhập/Đăng ký

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Đăng nhập/Đăng ký |
| **Số trường hợp kiểm thử đạt (P)** | 8 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 8 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 1 | Đăng nhập thành công với tài khoản Admin | 1. Truy cập trang đăng nhập<br>2. Nhập email: admin@example.com<br>3. Nhập password: admin123<br>4. Click nút "Đăng nhập" | Hiển thị thông báo thành công, chuyển đến trang Admin Dashboard | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 2 | Đăng nhập với email sai | 1. Truy cập trang đăng nhập<br>2. Nhập email: wrong@example.com<br>3. Nhập password: password<br>4. Click nút "Đăng nhập" | Hiển thị thông báo lỗi "Email hoặc mật khẩu không đúng" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 3 | Đăng nhập với mật khẩu sai | 1. Truy cập trang đăng nhập<br>2. Nhập email: admin@example.com<br>3. Nhập password: wrongpass<br>4. Click nút "Đăng nhập" | Hiển thị thông báo lỗi "Email hoặc mật khẩu không đúng" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 4 | Đăng nhập với trường rỗng | 1. Truy cập trang đăng nhập<br>2. Để trống email và password<br>3. Click nút "Đăng nhập" | Hiển thị thông báo lỗi validation "Email và mật khẩu là bắt buộc" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 5 | Đăng ký thành công | 1. Truy cập trang đăng ký<br>2. Nhập đầy đủ thông tin: Tên, Email, Mã sinh viên, Password<br>3. Click nút "Đăng ký" | Hiển thị thông báo thành công, tạo tài khoản và chuyển đến trang đăng nhập | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 6 | Đăng ký với email đã tồn tại | 1. Truy cập trang đăng ký<br>2. Nhập email đã tồn tại trong hệ thống<br>3. Nhập các thông tin khác<br>4. Click nút "Đăng ký" | Hiển thị thông báo lỗi "Email đã được sử dụng" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 7 | Đăng ký với mã sinh viên đã tồn tại | 1. Truy cập trang đăng ký<br>2. Nhập mã sinh viên đã tồn tại<br>3. Nhập các thông tin khác<br>4. Click nút "Đăng ký" | Hiển thị thông báo lỗi "Mã sinh viên đã được sử dụng" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 8 | Đăng ký với mật khẩu quá ngắn | 1. Truy cập trang đăng ký<br>2. Nhập mật khẩu: 123<br>3. Click nút "Đăng ký" | Hiển thị thông báo lỗi "Mật khẩu tối thiểu 6 ký tự" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý người dùng (Admin)

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý người dùng (Admin) |
| **Số trường hợp kiểm thử đạt (P)** | 7 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 7 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 9 | Xem danh sách người dùng | 1. Admin đăng nhập<br>2. Truy cập menu "Quản lý người dùng"<br>3. Xem danh sách người dùng | Hiển thị danh sách người dùng với phân trang, đầy đủ thông tin | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 10 | Tìm kiếm người dùng theo tên | 1. Admin đăng nhập<br>2. Truy cập trang quản lý người dùng<br>3. Nhập tên vào ô tìm kiếm<br>4. Click nút tìm kiếm | Hiển thị kết quả tìm kiếm chính xác theo tên | Pass | | Đã kiểm tra | Đã hoàn thành | | Trung bình |
| 11 | Tạo người dùng mới thành công | 1. Admin đăng nhập<br>2. Click nút "Tạo người dùng mới"<br>3. Nhập đầy đủ thông tin hợp lệ<br>4. Click nút "Lưu" | Hiển thị thông báo "Tạo người dùng thành công", người dùng xuất hiện trong danh sách | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 12 | Tạo người dùng với email không hợp lệ | 1. Admin đăng nhập<br>2. Click nút "Tạo người dùng mới"<br>3. Nhập email không đúng định dạng<br>4. Click nút "Lưu" | Hiển thị thông báo lỗi validation "Email không đúng định dạng" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 13 | Cập nhật thông tin người dùng | 1. Admin đăng nhập<br>2. Chọn một người dùng trong danh sách<br>3. Click nút "Chỉnh sửa"<br>4. Cập nhật thông tin<br>5. Click nút "Lưu" | Hiển thị thông báo "Cập nhật thành công", thông tin được cập nhật | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 14 | Reset mật khẩu người dùng | 1. Admin đăng nhập<br>2. Chọn một người dùng<br>3. Click nút "Reset mật khẩu"<br>4. Xác nhận reset | Hiển thị thông báo "Đã reset mật khẩu thành công", mật khẩu được reset về mặc định | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 15 | Xóa người dùng (soft delete) | 1. Admin đăng nhập<br>2. Chọn một người dùng<br>3. Click nút "Xóa"<br>4. Xác nhận xóa | Hiển thị thông báo "Xóa thành công", người dùng chuyển sang trạng thái đã xóa, có thể khôi phục | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý câu lạc bộ (Admin)

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý câu lạc bộ (Admin) |
| **Số trường hợp kiểm thử đạt (P)** | 9 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 9 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 16 | Xem danh sách câu lạc bộ | 1. Admin đăng nhập<br>2. Truy cập menu "Quản lý câu lạc bộ"<br>3. Xem danh sách CLB | Hiển thị danh sách CLB với phân trang, đầy đủ thông tin | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 17 | Tạo câu lạc bộ mới thành công | 1. Admin đăng nhập<br>2. Click nút "Tạo CLB mới"<br>3. Nhập đầy đủ thông tin: Tên, Mô tả, Lĩnh vực, Chủ CLB<br>4. Click nút "Lưu" | Hiển thị thông báo "Tạo CLB thành công", CLB xuất hiện trong danh sách với trạng thái "active" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 18 | Tạo CLB với tên trùng | 1. Admin đăng nhập<br>2. Click nút "Tạo CLB mới"<br>3. Nhập tên CLB đã tồn tại<br>4. Click nút "Lưu" | Hiển thị thông báo lỗi "Tên câu lạc bộ đã tồn tại" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 19 | Duyệt câu lạc bộ | 1. Admin đăng nhập<br>2. Xem danh sách CLB có trạng thái "pending"<br>3. Chọn một CLB<br>4. Click nút "Duyệt" | Trạng thái CLB chuyển sang "active", hiển thị thông báo "Duyệt thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 20 | Từ chối câu lạc bộ | 1. Admin đăng nhập<br>2. Xem danh sách CLB có trạng thái "pending"<br>3. Chọn một CLB<br>4. Click nút "Từ chối" | Trạng thái CLB chuyển sang "rejected", hiển thị thông báo "Đã từ chối" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 21 | Duyệt yêu cầu tham gia CLB | 1. Admin đăng nhập<br>2. Xem danh sách yêu cầu tham gia CLB<br>3. Chọn một yêu cầu<br>4. Click nút "Duyệt" | Thành viên được thêm vào CLB, trạng thái chuyển sang "approved", hiển thị thông báo thành công | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 22 | Từ chối yêu cầu tham gia CLB | 1. Admin đăng nhập<br>2. Xem danh sách yêu cầu tham gia CLB<br>3. Chọn một yêu cầu<br>4. Click nút "Từ chối" | Yêu cầu bị từ chối, trạng thái chuyển sang "rejected", hiển thị thông báo | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 23 | Xóa thành viên khỏi CLB | 1. Admin đăng nhập<br>2. Chọn một CLB<br>3. Xem danh sách thành viên<br>4. Chọn một thành viên<br>5. Click nút "Xóa thành viên"<br>6. Xác nhận xóa | Thành viên bị xóa khỏi CLB, hiển thị thông báo "Xóa thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 24 | Thay đổi vai trò thành viên | 1. Admin đăng nhập<br>2. Chọn một CLB<br>3. Xem danh sách thành viên<br>4. Chọn một thành viên<br>5. Thay đổi vai trò (Trưởng CLB, Phó CLB, Cán sự, Thành viên)<br>6. Click nút "Lưu" | Vai trò được cập nhật, hiển thị thông báo "Cập nhật thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý sự kiện (Admin)

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý sự kiện (Admin) |
| **Số trường hợp kiểm thử đạt (P)** | 5 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 5 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 25 | Xem danh sách sự kiện | 1. Admin đăng nhập<br>2. Truy cập menu "Quản lý sự kiện"<br>3. Xem danh sách sự kiện | Hiển thị danh sách sự kiện với phân trang, đầy đủ thông tin | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 26 | Tạo sự kiện mới thành công | 1. Admin đăng nhập<br>2. Click nút "Tạo sự kiện mới"<br>3. Nhập đầy đủ thông tin: Tên, Mô tả, Thời gian bắt đầu, Thời gian kết thúc, CLB<br>4. Click nút "Lưu" | Hiển thị thông báo "Tạo sự kiện thành công", sự kiện xuất hiện với trạng thái "pending" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 27 | Tạo sự kiện với thời gian không hợp lệ | 1. Admin đăng nhập<br>2. Click nút "Tạo sự kiện mới"<br>3. Nhập thời gian bắt đầu sau thời gian kết thúc<br>4. Click nút "Lưu" | Hiển thị thông báo lỗi "Thời gian bắt đầu phải trước thời gian kết thúc" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 28 | Duyệt sự kiện | 1. Admin đăng nhập<br>2. Xem danh sách sự kiện có trạng thái "pending"<br>3. Chọn một sự kiện<br>4. Click nút "Duyệt" | Trạng thái sự kiện chuyển sang "approved", hiển thị thông báo "Duyệt thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 29 | Hủy sự kiện | 1. Admin đăng nhập<br>2. Chọn một sự kiện<br>3. Click nút "Hủy sự kiện"<br>4. Nhập lý do hủy<br>5. Xác nhận hủy | Trạng thái sự kiện chuyển sang "cancelled", hiển thị thông báo "Đã hủy sự kiện" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý bài viết (Admin)

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý bài viết (Admin) |
| **Số trường hợp kiểm thử đạt (P)** | 5 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 5 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 30 | Xem danh sách bài viết | 1. Admin đăng nhập<br>2. Truy cập menu "Quản lý bài viết"<br>3. Xem danh sách bài viết | Hiển thị danh sách bài viết với phân trang, đầy đủ thông tin | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 31 | Tạo bài viết mới thành công | 1. Admin đăng nhập<br>2. Click nút "Tạo bài viết mới"<br>3. Nhập tiêu đề, nội dung, chọn CLB<br>4. Upload ảnh (nếu có)<br>5. Click nút "Lưu" | Hiển thị thông báo "Tạo bài viết thành công", bài viết xuất hiện trong danh sách | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 32 | Upload ảnh bài viết | 1. Admin đăng nhập<br>2. Tạo bài viết mới<br>3. Click nút "Chọn ảnh"<br>4. Chọn file ảnh hợp lệ (jpg, png)<br>5. Upload | Ảnh được upload thành công, hiển thị preview ảnh | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 33 | Upload file không hợp lệ | 1. Admin đăng nhập<br>2. Tạo bài viết mới<br>3. Click nút "Chọn ảnh"<br>4. Chọn file không hợp lệ (exe, php)<br>5. Upload | Hiển thị thông báo lỗi "File không được phép. Chỉ chấp nhận file ảnh" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 34 | Xóa bài viết | 1. Admin đăng nhập<br>2. Chọn một bài viết<br>3. Click nút "Xóa"<br>4. Xác nhận xóa | Hiển thị thông báo "Xóa thành công", bài viết chuyển vào thùng rác, có thể khôi phục | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý quỹ (Admin)

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý quỹ (Admin) |
| **Số trường hợp kiểm thử đạt (P)** | 7 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 7 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 35 | Xem danh sách quỹ | 1. Admin đăng nhập<br>2. Truy cập menu "Quản lý quỹ"<br>3. Xem danh sách quỹ | Hiển thị danh sách quỹ với thông tin: Tên quỹ, Số tiền hiện có, CLB | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 36 | Tạo quỹ mới thành công | 1. Admin đăng nhập<br>2. Click nút "Tạo quỹ mới"<br>3. Nhập tên quỹ, chọn CLB, số tiền ban đầu<br>4. Click nút "Lưu" | Hiển thị thông báo "Tạo quỹ thành công", quỹ xuất hiện trong danh sách | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 37 | Duyệt yêu cầu cấp kinh phí | 1. Admin đăng nhập<br>2. Xem danh sách yêu cầu cấp kinh phí có trạng thái "pending"<br>3. Chọn một yêu cầu<br>4. Click nút "Duyệt"<br>5. Nhập số tiền duyệt<br>6. Xác nhận | Trạng thái yêu cầu chuyển sang "approved", quỹ được cập nhật, hiển thị thông báo thành công | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 38 | Từ chối yêu cầu cấp kinh phí | 1. Admin đăng nhập<br>2. Xem danh sách yêu cầu cấp kinh phí<br>3. Chọn một yêu cầu<br>4. Click nút "Từ chối"<br>5. Nhập lý do từ chối<br>6. Xác nhận | Trạng thái yêu cầu chuyển sang "rejected", hiển thị thông báo "Đã từ chối" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 39 | Quyết toán kinh phí với số tiền khớp | 1. Admin đăng nhập<br>2. Chọn một yêu cầu đã được duyệt<br>3. Click nút "Quyết toán"<br>4. Nhập số tiền chi thực tế = số tiền duyệt<br>5. Upload hóa đơn/hình ảnh<br>6. Click nút "Lưu" | Tạo giao dịch CHI, cập nhật quỹ, hiển thị thông báo "Quyết toán thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 40 | Quyết toán với tiền thừa | 1. Admin đăng nhập<br>2. Chọn một yêu cầu đã được duyệt<br>3. Click nút "Quyết toán"<br>4. Nhập số tiền chi thực tế < số tiền duyệt<br>5. Upload hóa đơn<br>6. Click nút "Lưu" | Tạo giao dịch CHI và giao dịch THU (hoàn tiền thừa), cập nhật quỹ, hiển thị thông báo thành công | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 41 | Quyết toán với tiền thiếu | 1. Admin đăng nhập<br>2. Chọn một yêu cầu đã được duyệt<br>3. Click nút "Quyết toán"<br>4. Nhập số tiền chi thực tế > số tiền duyệt<br>5. Upload hóa đơn<br>6. Click nút "Lưu" | Tạo giao dịch CHI, hiển thị cảnh báo về số tiền vượt quá, hiển thị thông báo thành công | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Sinh viên tham gia CLB

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Sinh viên tham gia CLB |
| **Số trường hợp kiểm thử đạt (P)** | 5 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 5 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 42 | Tìm kiếm câu lạc bộ | 1. Sinh viên đăng nhập<br>2. Truy cập trang "Danh sách CLB"<br>3. Nhập từ khóa tìm kiếm<br>4. Click nút tìm kiếm | Hiển thị kết quả tìm kiếm chính xác theo từ khóa | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 43 | Xem thông tin chi tiết CLB | 1. Sinh viên đăng nhập<br>2. Truy cập trang "Danh sách CLB"<br>3. Click vào một CLB | Hiển thị thông tin chi tiết CLB: Mô tả, Thành viên, Sự kiện, Bài viết | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 44 | Đăng ký tham gia CLB | 1. Sinh viên đăng nhập<br>2. Xem thông tin chi tiết một CLB<br>3. Click nút "Tham gia CLB"<br>4. Xác nhận | Hiển thị thông báo "Đã gửi yêu cầu tham gia", yêu cầu có trạng thái "pending" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 45 | Hủy yêu cầu tham gia CLB | 1. Sinh viên đăng nhập<br>2. Xem danh sách yêu cầu tham gia CLB của mình<br>3. Chọn một yêu cầu có trạng thái "pending"<br>4. Click nút "Hủy yêu cầu"<br>5. Xác nhận | Yêu cầu bị hủy, hiển thị thông báo "Đã hủy yêu cầu" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 46 | Rời khỏi CLB | 1. Sinh viên đăng nhập<br>2. Xem danh sách CLB đã tham gia<br>3. Chọn một CLB<br>4. Click nút "Rời khỏi CLB"<br>5. Xác nhận | Hiển thị thông báo "Đã rời khỏi CLB", sinh viên không còn trong danh sách thành viên | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Sinh viên đăng ký sự kiện

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Sinh viên đăng ký sự kiện |
| **Số trường hợp kiểm thử đạt (P)** | 5 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 5 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 47 | Xem danh sách sự kiện | 1. Sinh viên đăng nhập<br>2. Truy cập trang "Sự kiện"<br>3. Xem danh sách sự kiện | Hiển thị danh sách sự kiện với phân trang, đầy đủ thông tin | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 48 | Xem thông tin chi tiết sự kiện | 1. Sinh viên đăng nhập<br>2. Click vào một sự kiện trong danh sách | Hiển thị thông tin chi tiết sự kiện: Mô tả, Thời gian, Địa điểm, Số người đã đăng ký | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 49 | Đăng ký tham gia sự kiện thành công | 1. Sinh viên đăng nhập<br>2. Xem thông tin chi tiết một sự kiện<br>3. Click nút "Đăng ký tham gia"<br>4. Xác nhận | Hiển thị thông báo "Đăng ký thành công", sinh viên xuất hiện trong danh sách đăng ký | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 50 | Hủy đăng ký sự kiện | 1. Sinh viên đăng nhập<br>2. Xem danh sách sự kiện đã đăng ký<br>3. Chọn một sự kiện<br>4. Click nút "Hủy đăng ký"<br>5. Xác nhận | Hiển thị thông báo "Đã hủy đăng ký", sinh viên không còn trong danh sách đăng ký | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 51 | Đăng ký khi sự kiện đã đầy | 1. Sinh viên đăng nhập<br>2. Xem thông tin chi tiết một sự kiện đã đầy<br>3. Click nút "Đăng ký tham gia" | Hiển thị thông báo "Sự kiện đã đầy, không thể đăng ký thêm" | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |

---

### Chức năng : Sinh viên đăng bài viết

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Sinh viên đăng bài viết |
| **Số trường hợp kiểm thử đạt (P)** | 3 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 3 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 52 | Tạo bài viết mới thành công | 1. Sinh viên đăng nhập<br>2. Truy cập trang "Bài viết"<br>3. Click nút "Tạo bài viết mới"<br>4. Nhập tiêu đề, nội dung<br>5. Upload ảnh (nếu có)<br>6. Click nút "Đăng bài" | Hiển thị thông báo "Đăng bài thành công", bài viết xuất hiện trong danh sách | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 53 | Bình luận bài viết | 1. Sinh viên đăng nhập<br>2. Xem chi tiết một bài viết<br>3. Nhập nội dung bình luận<br>4. Click nút "Gửi bình luận" | Bình luận được thêm vào, hiển thị ngay dưới bài viết | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 54 | Xóa bình luận của mình | 1. Sinh viên đăng nhập<br>2. Xem chi tiết một bài viết có bình luận của mình<br>3. Click nút "Xóa" trên bình luận<br>4. Xác nhận xóa | Bình luận bị xóa, hiển thị thông báo "Đã xóa bình luận" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Dashboard và Thống kê

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Dashboard và Thống kê |
| **Số trường hợp kiểm thử đạt (P)** | 3 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 3 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 55 | Xem Dashboard Admin | 1. Admin đăng nhập<br>2. Truy cập trang Dashboard | Hiển thị thống kê tổng quan: Số lượng users, clubs, events, posts, biểu đồ thống kê | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 56 | Lọc thống kê theo khoảng thời gian | 1. Admin đăng nhập<br>2. Truy cập trang Dashboard<br>3. Chọn khoảng thời gian (Hôm nay, Hôm qua, Tuần này, Tháng này)<br>4. Xem kết quả | Dữ liệu thống kê được lọc chính xác theo khoảng thời gian đã chọn | Pass | | Đã kiểm tra | Đã hoàn thành | | Trung bình |
| 57 | Xem Dashboard Sinh viên | 1. Sinh viên đăng nhập<br>2. Truy cập trang Dashboard | Hiển thị thông tin cá nhân, CLB đã tham gia, sự kiện sắp tới, thông báo | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Bảo mật và Phân quyền

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Bảo mật và Phân quyền |
| **Số trường hợp kiểm thử đạt (P)** | 5 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 5 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 58 | Truy cập trang Admin khi chưa đăng nhập | 1. Mở trình duyệt mới (chưa đăng nhập)<br>2. Truy cập URL: /admin/dashboard | Tự động chuyển hướng đến trang đăng nhập, hiển thị thông báo "Vui lòng đăng nhập" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 59 | Sinh viên cố truy cập trang Admin | 1. Sinh viên đăng nhập<br>2. Cố truy cập URL: /admin/dashboard | Bị từ chối, hiển thị lỗi 403 "Bạn không có quyền truy cập" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 60 | Sinh viên cố truy cập quản lý CLB không phải của mình | 1. Sinh viên đăng nhập (không phải trưởng CLB)<br>2. Cố truy cập URL quản lý CLB khác | Bị từ chối, hiển thị lỗi 403 "Bạn không có quyền quản lý CLB này" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 61 | Cấp quyền Trưởng CLB cho người dùng | 1. Admin đăng nhập<br>2. Truy cập trang "Phân quyền"<br>3. Chọn một người dùng<br>4. Chọn một CLB<br>5. Cấp quyền "Trưởng CLB"<br>6. Click nút "Lưu" | Người dùng có quyền quản lý CLB, hiển thị thông báo "Cấp quyền thành công" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 62 | Thu hồi quyền người dùng | 1. Admin đăng nhập<br>2. Truy cập trang "Phân quyền"<br>3. Chọn một người dùng có quyền<br>4. Click nút "Thu hồi quyền"<br>5. Xác nhận | Quyền được thu hồi, hiển thị thông báo "Đã thu hồi quyền" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

### Chức năng : Quản lý tài nguyên CLB

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý tài nguyên CLB |
| **Số trường hợp kiểm thử đạt (P)** | 3 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 3 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 63 | Tải xuống tài liệu học tập | 1. Sinh viên đăng nhập<br>2. Truy cập trang CLB<br>3. Xem danh sách tài nguyên<br>4. Click nút "Tải xuống" trên một tài liệu | File được tải xuống thành công, hiển thị thông báo "Đang tải xuống" | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 64 | Upload tài liệu học tập (Trưởng CLB) | 1. Trưởng CLB đăng nhập<br>2. Truy cập trang quản lý CLB<br>3. Click nút "Thêm tài liệu"<br>4. Chọn file (PDF, DOC, DOCX)<br>5. Nhập tên và mô tả<br>6. Click nút "Upload" | Tài liệu được upload thành công, hiển thị trong danh sách tài nguyên | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 65 | Xóa tài liệu học tập (Admin) | 1. Admin đăng nhập<br>2. Truy cập trang "Quản lý tài nguyên CLB"<br>3. Chọn một tài liệu<br>4. Click nút "Xóa"<br>5. Xác nhận xóa | Tài liệu chuyển vào thùng rác, hiển thị thông báo "Xóa thành công", có thể khôi phục | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

### Chức năng : Quản lý tin tức ký túc xá

| Thông tin | Giá trị |
|-----------|---------|
| **Tên màn hình/Tên chức năng** | Quản lý tin tức ký túc xá |
| **Số trường hợp kiểm thử đạt (P)** | 7 |
| **Số trường hợp kiểm thử không đạt (F)** | 0 |
| **Số trường hợp kiểm thử đang xem xét (PE)** | 0 |
| **Số trường hợp kiểm thử chưa thực hiện** | 0 |
| **Tổng số trường hợp kiểm thử** | 7 |

| STT | Mục đích kiểm thử | Các bước thực hiện | Kết quả mong muốn | Kết quả hiện tại | Mã lỗi | Ghi chú | Trạng thái | Người fix | Độ ưu tiên |
|-----|-------------------|-------------------|-------------------|------------------|--------|---------|------------|-----------|------------|
| 66 | Admin tạo tin tức thành công | 1. Admin đăng nhập<br>2. Vào menu "Quản lý tin tức"<br>3. Click "Tạo tin mới"<br>4. Nhập tiêu đề, nội dung, ảnh (nếu có)<br>5. Chọn phạm vi đăng<br>6. Click "Lưu" | Hiển thị thông báo "Đăng tin thành công", tin mới xuất hiện trong danh sách | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 67 | Tạo tin tức không nhập tiêu đề | 1. Admin đăng nhập<br>2. Vào tạo tin mới<br>3. Để trống tiêu đề<br>4. Nhập nội dung<br>5. Click "Lưu" | Hiển thị lỗi "Tiêu đề là bắt buộc", tin không được lưu | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 68 | Tạo tin tức không nhập nội dung | 1. Admin đăng nhập<br>2. Vào tạo tin mới<br>3. Nhập tiêu đề<br>4. Để trống nội dung<br>5. Click "Lưu" | Hiển thị lỗi "Nội dung không được để trống", tin không được tạo | Pass | | Validate dữ liệu | Đã hoàn thành | | Cao |
| 69 | Hiển thị danh sách tin tức | 1. Người dùng đăng nhập<br>2. Truy cập mục "Tin tức ký túc xá"<br>3. Xem danh sách | Danh sách tin tức hiển thị đúng theo ngày, có ảnh minh họa, phân trang hoạt động | Pass | | Đã kiểm tra | Đã hoàn thành | | Trung bình |
| 70 | Sinh viên xem chi tiết tin tức | 1. Sinh viên vào mục tin tức<br>2. Click vào tin | Hiển thị đầy đủ tiêu đề, nội dung, tác giả, thời gian đăng, có ảnh hiển thị đúng | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |
| 71 | Lưu tin yêu thích | 1. Sinh viên xem tin<br>2. Click "Lưu tin"<br>3. Vào mục "Tin đã lưu" | Tin được thêm vào danh sách tin yêu thích, thông báo "Đã lưu tin" xuất hiện | Pass | | Đã kiểm tra | Đã hoàn thành | | Trung bình |
| 72 | Xóa tin tức (Admin) | 1. Admin chọn tin<br>2. Click "Xóa"<br>3. Xác nhận | Tin chuyển vào thùng rác, không hiển thị trên trang sinh viên, có thể khôi phục | Pass | | Đã kiểm tra | Đã hoàn thành | | Cao |

---

## TỔNG KẾT

- **Tổng số test case**: 72
- **Số test case đạt (Pass)**: 72
- **Số test case không đạt (Fail)**: 0
- **Số test case chưa thực hiện (Untested)**: 0
- **Số test case không áp dụng (N/A)**: 0
- **Tỷ lệ thành công**: 100%

---

# CHƯƠNG 7: KẾT LUẬN

## 7.1. Thời gian phát triển dự án

Dự án được triển khai từ ngày 17/09/2025 đến ngày 20/12/2025, chia thành các giai đoạn chính như sau:

- **17/09/2025 – 30/09/2025**: Khảo sát và xác định quy trình dự án để nắm rõ yêu cầu nghiệp vụ quản lý câu lạc bộ sinh viên.
- **03/10/2025 – 11/10/2025**: Phân tích chi tiết hệ thống và lên danh sách chức năng cốt lõi.
- **13/10/2025 – 17/10/2025**: Thiết kế sơ đồ ERD và dựng cơ sở dữ liệu dùng chung cho module người dùng, CLB, sự kiện, quỹ và bài viết.
- **19/10/2025 – 07/11/2025**: Phát triển các chức năng Admin: quản lý người dùng, CLB, thành viên, sự kiện, bài viết, quỹ, phân quyền, tài nguyên và thống kê.
- **09/11/2025 – 18/11/2025**: Code giao diện người dùng bao gồm trang chủ công khai, đăng nhập/đăng ký, tin tức/thông báo, giới thiệu, hướng dẫn, trang quản lý cá nhân/vai trò CLB, trang sự kiện, bài viết, quỹ, phân quyền và thông báo nội bộ.
- **20/11/2025 – 30/11/2025**: Hoàn thiện giao diện, tích hợp dữ liệu và kiểm thử toàn bộ chức năng admin+user.
- **02/12/2025 – 20/12/2025**: Hoàn chỉnh báo cáo, tiếp thu góp ý và chỉnh sửa phần còn thiếu trước nghiệm thu.

## 7.2. Kết luận tổng thể

Hệ thống quản lý câu lạc bộ sinh viên đã hoàn thiện các chức năng đặc thù: quản lý người dùng/CLB/thành viên, sự kiện, bài viết, quỹ, phân quyền, tài nguyên và thống kê; đồng thời cung cấp giao diện người dùng cho trang chủ, đăng nhập/đăng ký, tin tức/thông báo, hướng dẫn, quản lý hồ sơ CLB, theo dõi quỹ/sự kiện và hệ thống thông báo nội bộ. Chuỗi 72 test case confirm mỗi luồng hoạt động đúng như kịch bản mô tả, dữ liệu được validate và các hành động CRUD đều có phản hồi rõ ràng. Dự án đã sẵn sàng đưa vào vận hành, chỉ cần duy trì theo dõi các cảnh báo nhỏ để đảm bảo độ ổn định dài hạn.

---

# PHỤ LỤC: KHẢO SÁT NHU CẦU HỆ THỐNG CLB

| STT | Câu hỏi | Câu trả lời mẫu |
|-----|----------|----------------|
| 1 | Bạn đang giữ vai trò nào trong CLB? | Trưởng CLB / Phó CLB / Cán sự / Thành viên / Khác (ghi rõ) |
| 2 | CLB bạn quản lý hiện có bao nhiêu thành viên hoạt động chính thức? | Dưới 30 / 30-60 / 60-100 / Trên 100 |
| 3 | Chức năng nào bạn dùng thường xuyên? | Đăng bài, quản lý thành viên, tạo sự kiện, duyệt yêu cầu, quản lý quỹ |
| 4 | Bạn gặp khó khăn gì khi duyệt yêu cầu tham gia CLB? | Chưa thấy yêu cầu đúng mẫu / Khó theo dõi trạng thái / Không nhận được thông báo |
| 5 | Giao diện sự kiện hiện tại đã đủ trường thông tin chưa? | Đủ / Cần thêm trường địa điểm / Cần thêm khả năng upload tài liệu |
| 6 | Bạn muốn bổ sung gì cho phần quản lý tài chính/quỹ? | Báo cáo thu chi theo kỳ / Đóng góp tự động / Phân quyền duyệt nhiều cấp |
| 7 | Bạn có cần báo cáo/thống kê thêm mục nào không? | Theo số thành viên, tỷ lệ tham gia sự kiện, mức độ hoàn thành nhiệm vụ |
| 8 | Giao diện admin có dễ dùng không? | Rất dễ / Khá dễ / Trung bình / Cần hướng dẫn / Khó thao tác |
| 9 | Bạn cần thông báo riêng cho CLB không? | Có (nội bộ, deadline) / Không cần |
| 10 | Mức độ hài lòng tổng thể với hệ thống? | Rất hài lòng / Hài lòng / Trung lập / Chưa hài lòng / Không hài lòng |
| 11 | Ý kiến đóng góp khác | (Trống để ghi ý kiến cụ thể về tính năng mong muốn, cải tiến UX, performance...) |

Đây là mẫu khảo sát để thu thập ý kiến các CLB về yêu cầu hệ thống. Bạn có thể gửi link form hoặc đánh dấu trực tiếp vào bảng này trước khi triển khai từng vòng nâng cấp. Nếu cần mình cũng có thể chuyển sang dạng Google Form hay Excel.

### Mẫu trả lời giả lập từ 3 CLB

| CLB | Vai trò trả lời | Số thành viên | Chức năng dùng | Khó khăn | Đề xuất sự kiện | Quỹ | Thống kê | Admin UI | Thông báo | Mức hài lòng | Góp ý khác |
|-----|-----------------|----------------|---------------|----------|----------------|-----|---------|----------|--------------|-----------|
| CLB Nghệ thuật | Trưởng CLB | 45 | Đăng bài, tạo event, quản lý thành viên | Khó theo dõi yêu cầu pending | Cần thêm upload hình ảnh sự kiện | Cần báo cáo thu chi theo tháng | Mức độ tham gia hoạt động | Dễ thao tác | Cần thông báo nội bộ | Hài lòng | Muốn gửi thông báo bằng email |
| CLB Khoa học | Cán sự | 32 | Duyệt thành viên, tạo quỹ, thống kê | Không nhận được email cập nhật yêu cầu | Thêm trường mô tả nhiệm vụ | Muốn phân cấp duyệt quỹ | Mong báo cáo sự kiện theo tuần | Khá dễ | Cần thông báo nhắc deadline | Rất hài lòng | Thêm chức năng xuất CSV |
| CLB Thể thao | Phó CLB | 80 | Quản lý sự kiện, bài viết, thông báo | Khó kiểm tra validation thông tin đăng ký | Muốn upload tài liệu hướng dẫn | Theo dõi chi tiết chi tiêu | Theo dõi tỷ lệ tham gia | Cần hướng dẫn nhanh | Có nội bộ gấp | Hài lòng | Cần dashboard live số người tham gia |

