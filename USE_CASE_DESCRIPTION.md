# MÔ TẢ USE CASE - HỆ THỐNG QUẢN LÝ CÂU LẠC BỘ SINH VIÊN

## 1. QUẢN LÝ XÁC THỰC VÀ PHÂN QUYỀN

### UC-001: Đăng nhập Admin
**Mô tả chung:** Admin đăng nhập vào hệ thống để truy cập admin panel
- **Input:**
  - Email/Tên đăng nhập
  - Mật khẩu
- **Output:**
  - Session đăng nhập thành công
  - Redirect đến admin dashboard
  - Thông báo lỗi nếu thông tin không đúng

### UC-002: Đăng nhập Sinh viên
**Mô tả chung:** Sinh viên đăng nhập vào hệ thống
- **Input:**
  - Email
  - Mật khẩu
- **Output:**
  - Session đăng nhập thành công
  - Redirect đến student dashboard
  - Thông báo lỗi nếu thông tin không đúng

### UC-003: Đăng ký tài khoản sinh viên
**Mô tả chung:** Sinh viên đăng ký tài khoản mới hoặc Admin tạo tài khoản cho sinh viên
- **Input:**
  - Tên
  - Email
  - Mã sinh viên (student_id)
  - Mật khẩu
- **Output:**
  - Tài khoản được tạo thành công
  - Thông báo thành công/lỗi
  - Redirect đến trang đăng nhập hoặc danh sách người dùng

### UC-004: Đăng xuất
**Mô tả chung:** Người dùng đăng xuất khỏi hệ thống
- **Input:**
  - Request đăng xuất
- **Output:**
  - Session bị xóa
  - Redirect đến trang đăng nhập

---

## 2. QUẢN LÝ NGƯỜI DÙNG (ADMIN)

### UC-005: Xem danh sách người dùng
**Mô tả chung:** Admin xem danh sách tất cả người dùng trong hệ thống
- **Input:**
  - Request GET /admin/users
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách người dùng (phân trang)
  - Thông tin: Tên, Email, Mã sinh viên, Vai trò, Trạng thái
  - Tổng số người dùng

### UC-006: Tạo tài khoản người dùng
**Mô tả chung:** Admin tạo tài khoản mới cho người dùng
- **Input:**
  - Tên (required)
  - Email (required, unique)
  - Mã sinh viên (required, unique)
  - Mật khẩu (required, min:6)
  - Vai trò (admin/student)
- **Output:**
  - Tài khoản được tạo thành công
  - Redirect đến danh sách người dùng
  - Thông báo lỗi validation nếu có

### UC-007: Xem chi tiết người dùng
**Mô tả chung:** Admin xem thông tin chi tiết của một người dùng
- **Input:**
  - ID người dùng
- **Output:**
  - Thông tin đầy đủ người dùng
  - Danh sách CLB tham gia
  - Lịch sử hoạt động
  - Quyền hạn

### UC-008: Cập nhật thông tin người dùng
**Mô tả chung:** Admin chỉnh sửa thông tin người dùng
- **Input:**
  - ID người dùng
  - Tên (tùy chọn)
  - Email (tùy chọn)
  - Mã sinh viên (tùy chọn)
  - Trạng thái (active/inactive)
- **Output:**
  - Thông tin được cập nhật thành công
  - Redirect đến trang chi tiết người dùng
  - Thông báo lỗi nếu có

### UC-009: Xóa người dùng
**Mô tả chung:** Admin xóa người dùng khỏi hệ thống
- **Input:**
  - ID người dùng
- **Output:**
  - Người dùng bị xóa thành công
  - Redirect đến danh sách người dùng
  - Thông báo lỗi nếu người dùng có dữ liệu liên quan

### UC-010: Reset mật khẩu người dùng
**Mô tả chung:** Admin reset mật khẩu cho người dùng
- **Input:**
  - ID người dùng
  - Mật khẩu mới (tùy chọn)
- **Output:**
  - Mật khẩu được reset thành công
  - Mật khẩu mặc định được gửi (nếu có)
  - Thông báo thành công

---

## 3. QUẢN LÝ CÂU LẠC BỘ

### UC-011: Xem danh sách câu lạc bộ (Admin)
**Mô tả chung:** Admin xem danh sách tất cả câu lạc bộ
- **Input:**
  - Request GET /admin/clubs
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách câu lạc bộ (phân trang)
  - Thông tin: Tên, Mô tả, Chủ CLB, Trạng thái, Số thành viên
  - Tổng số CLB

### UC-012: Tạo câu lạc bộ mới
**Mô tả chung:** Admin tạo câu lạc bộ mới
- **Input:**
  - Tên CLB (required)
  - Mô tả (tùy chọn)
  - ID chủ CLB (required, exists:users,id)
  - Danh sách ban cán sự (array, tùy chọn)
  - Lĩnh vực (field_id, tùy chọn)
- **Output:**
  - CLB được tạo thành công
  - Chủ CLB được thêm với role 'owner'
  - Ban cán sự được thêm với role 'executive_board'
  - Redirect đến danh sách CLB
  - Thông báo lỗi validation nếu có

### UC-013: Xem chi tiết câu lạc bộ
**Mô tả chung:** Admin/Sinh viên xem thông tin chi tiết câu lạc bộ
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - Thông tin đầy đủ CLB
  - Danh sách thành viên
  - Danh sách sự kiện
  - Danh sách bài viết
  - Thông tin quỹ (nếu có)

### UC-014: Cập nhật thông tin câu lạc bộ
**Mô tả chung:** Admin cập nhật thông tin câu lạc bộ
- **Input:**
  - ID câu lạc bộ
  - Tên (tùy chọn)
  - Mô tả (tùy chọn)
  - Logo (file, tùy chọn)
  - Trạng thái (active/pending/inactive)
- **Output:**
  - Thông tin được cập nhật thành công
  - Redirect đến trang chi tiết CLB
  - Thông báo lỗi nếu có

### UC-015: Xóa câu lạc bộ
**Mô tả chung:** Admin xóa câu lạc bộ (soft delete)
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - CLB bị xóa thành công (soft delete)
  - Redirect đến danh sách CLB
  - Thông báo lỗi nếu CLB có dữ liệu quan trọng

### UC-016: Cập nhật trạng thái câu lạc bộ
**Mô tả chung:** Admin thay đổi trạng thái CLB (active/pending/inactive)
- **Input:**
  - ID câu lạc bộ
  - Trạng thái mới
- **Output:**
  - Trạng thái được cập nhật thành công
  - Redirect đến danh sách CLB
  - Thông báo thành công

### UC-017: Xem danh sách câu lạc bộ (Sinh viên)
**Mô tả chung:** Sinh viên xem danh sách câu lạc bộ có thể tham gia
- **Input:**
  - Request GET /student/clubs
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo lĩnh vực (tùy chọn)
  - Tham số sắp xếp (tùy chọn)
- **Output:**
  - Danh sách CLB (phân trang)
  - Thông tin: Tên, Mô tả, Số thành viên, Trạng thái tham gia
  - Nút tham gia/rời CLB

### UC-018: Tham gia câu lạc bộ
**Mô tả chung:** Sinh viên gửi yêu cầu tham gia câu lạc bộ
- **Input:**
  - ID câu lạc bộ
  - Lời nhắn (tùy chọn)
- **Output:**
  - Yêu cầu tham gia được gửi thành công
  - Trạng thái: pending
  - Thông báo thành công
  - Thông báo lỗi nếu đã là thành viên hoặc đã gửi yêu cầu

### UC-019: Rời câu lạc bộ
**Mô tả chung:** Sinh viên rời khỏi câu lạc bộ
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - Thành viên bị xóa khỏi CLB
  - Redirect đến danh sách CLB
  - Thông báo thành công
  - Thông báo lỗi nếu không phải thành viên

### UC-020: Hủy yêu cầu tham gia
**Mô tả chung:** Sinh viên hủy yêu cầu tham gia CLB đang chờ duyệt
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - Yêu cầu bị hủy thành công
  - Redirect đến danh sách CLB
  - Thông báo thành công

---

## 4. QUẢN LÝ THÀNH VIÊN CLB

### UC-021: Xem danh sách thành viên CLB
**Mô tả chung:** Admin/Chủ CLB/Ban cán sự xem danh sách thành viên CLB
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - Danh sách thành viên (phân trang)
  - Thông tin: Tên, Email, Vai trò, Vị trí, Trạng thái, Ngày tham gia
  - Tổng số thành viên

### UC-022: Thêm thành viên vào CLB
**Mô tả chung:** Admin thêm thành viên vào CLB (hỗ trợ thêm nhiều người cùng lúc)
- **Input:**
  - ID câu lạc bộ
  - Danh sách ID người dùng (array, required)
  - Vị trí (member/officer/leader, required)
- **Output:**
  - Số lượng thành viên được thêm thành công
  - Số lượng thành viên đã tồn tại (bỏ qua)
  - Thông báo lỗi nếu user đã là cán sự/trưởng ở CLB khác
  - Tự động cấp quyền nếu là leader
  - Tự động cấp quyền "xem_bao_cao" nếu là member

### UC-023: Xóa thành viên khỏi CLB
**Mô tả chung:** Admin/Chủ CLB/Ban cán sự xóa thành viên khỏi CLB
- **Input:**
  - ID câu lạc bộ
  - ID người dùng
- **Output:**
  - Thành viên bị xóa thành công
  - Redirect đến danh sách thành viên
  - Thông báo lỗi nếu là chủ CLB (không thể xóa)

### UC-024: Duyệt yêu cầu tham gia CLB
**Mô tả chung:** Chủ CLB/Ban cán sự duyệt yêu cầu tham gia CLB
- **Input:**
  - ID câu lạc bộ
  - ID yêu cầu
- **Output:**
  - Yêu cầu được duyệt thành công
  - Thành viên được thêm vào CLB với trạng thái 'approved'
  - Thông báo gửi đến sinh viên
  - Redirect đến danh sách yêu cầu

### UC-025: Từ chối yêu cầu tham gia CLB
**Mô tả chung:** Chủ CLB/Ban cán sự từ chối yêu cầu tham gia CLB
- **Input:**
  - ID câu lạc bộ
  - ID yêu cầu
  - Lý do từ chối (tùy chọn)
- **Output:**
  - Yêu cầu bị từ chối thành công
  - Thông báo gửi đến sinh viên
  - Redirect đến danh sách yêu cầu

### UC-026: Cập nhật vai trò thành viên
**Mô tả chung:** Admin/Chủ CLB cập nhật vai trò/vị trí của thành viên
- **Input:**
  - ID câu lạc bộ
  - ID thành viên
  - Vai trò mới (owner/executive_board/member)
  - Vị trí mới (leader/officer/member)
- **Output:**
  - Vai trò được cập nhật thành công
  - Quyền hạn được cập nhật tự động
  - Redirect đến danh sách thành viên
  - Thông báo lỗi nếu có

### UC-027: Cập nhật quyền hạn thành viên
**Mô tả chung:** Chủ CLB/Ban cán sự cập nhật quyền hạn của thành viên trong CLB
- **Input:**
  - ID câu lạc bộ
  - ID thành viên
  - Danh sách quyền (array of permission IDs)
- **Output:**
  - Quyền hạn được cập nhật thành công
  - Redirect đến danh sách thành viên
  - Thông báo thành công

---

## 5. QUẢN LÝ SỰ KIỆN

### UC-028: Xem danh sách sự kiện
**Mô tả chung:** Sinh viên/Admin xem danh sách sự kiện
- **Input:**
  - Request GET /student/events hoặc /admin/events
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách sự kiện (phân trang)
  - Thông tin: Tên, Mô tả, CLB, Thời gian, Địa điểm, Trạng thái, Số người đăng ký

### UC-029: Tạo sự kiện mới
**Mô tả chung:** Sinh viên/Admin tạo sự kiện mới
- **Input:**
  - Tên sự kiện (required)
  - Mô tả (required)
  - ID CLB (required)
  - Thời gian bắt đầu (required, datetime)
  - Thời gian kết thúc (required, datetime)
  - Địa điểm (required)
  - Số lượng người tham gia tối đa (tùy chọn)
  - Hình ảnh (file, tùy chọn)
- **Output:**
  - Sự kiện được tạo thành công với trạng thái 'pending'
  - Redirect đến danh sách sự kiện
  - Thông báo lỗi validation nếu có

### UC-030: Xem chi tiết sự kiện
**Mô tả chung:** Sinh viên/Admin xem thông tin chi tiết sự kiện
- **Input:**
  - ID sự kiện
- **Output:**
  - Thông tin đầy đủ sự kiện
  - Danh sách người đăng ký
  - Trạng thái đăng ký của user hiện tại (nếu đã đăng nhập)
  - Hình ảnh sự kiện

### UC-031: Cập nhật sự kiện
**Mô tả chung:** Người tạo/Admin cập nhật thông tin sự kiện
- **Input:**
  - ID sự kiện
  - Tên (tùy chọn)
  - Mô tả (tùy chọn)
  - Thời gian bắt đầu (tùy chọn)
  - Thời gian kết thúc (tùy chọn)
  - Địa điểm (tùy chọn)
  - Hình ảnh mới (file, tùy chọn)
- **Output:**
  - Sự kiện được cập nhật thành công
  - Redirect đến trang chi tiết sự kiện
  - Thông báo lỗi nếu có

### UC-032: Xóa sự kiện
**Mô tả chung:** Người tạo/Admin xóa sự kiện (soft delete)
- **Input:**
  - ID sự kiện
- **Output:**
  - Sự kiện bị xóa thành công
  - Redirect đến danh sách sự kiện
  - Thông báo thành công

### UC-033: Khôi phục sự kiện
**Mô tả chung:** Admin khôi phục sự kiện đã bị xóa
- **Input:**
  - ID sự kiện
- **Output:**
  - Sự kiện được khôi phục thành công
  - Redirect đến danh sách sự kiện
  - Thông báo thành công

### UC-034: Duyệt sự kiện
**Mô tả chung:** Admin duyệt sự kiện để cho phép hiển thị công khai
- **Input:**
  - ID sự kiện
- **Output:**
  - Trạng thái sự kiện chuyển thành 'approved'
  - Redirect đến trang chi tiết sự kiện
  - Thông báo thành công

### UC-035: Hủy sự kiện
**Mô tả chung:** Người tạo/Admin hủy sự kiện
- **Input:**
  - ID sự kiện
  - Lý do hủy (required)
- **Output:**
  - Trạng thái sự kiện chuyển thành 'cancelled'
  - Thông báo gửi đến tất cả người đăng ký
  - Redirect đến trang chi tiết sự kiện
  - Thông báo thành công

### UC-036: Đăng ký tham gia sự kiện
**Mô tả chung:** Sinh viên đăng ký tham gia sự kiện
- **Input:**
  - ID sự kiện
- **Output:**
  - Đăng ký thành công
  - Trạng thái: registered
  - Thông báo thành công
  - Thông báo lỗi nếu sự kiện đã đầy hoặc đã đăng ký

### UC-037: Hủy đăng ký sự kiện
**Mô tả chung:** Sinh viên hủy đăng ký tham gia sự kiện
- **Input:**
  - ID sự kiện
- **Output:**
  - Đăng ký bị hủy thành công
  - Redirect đến trang chi tiết sự kiện
  - Thông báo thành công
  - Thông báo lỗi nếu chưa đăng ký

---

## 6. QUẢN LÝ BÀI VIẾT

### UC-038: Xem danh sách bài viết
**Mô tả chung:** Sinh viên/Admin xem danh sách bài viết
- **Input:**
  - Request GET /student/posts hoặc /admin/posts
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách bài viết (phân trang)
  - Thông tin: Tiêu đề, Tác giả, CLB, Ngày đăng, Trạng thái, Số lượt xem

### UC-039: Tạo bài viết mới
**Mô tả chung:** Sinh viên/Admin tạo bài viết mới
- **Input:**
  - Tiêu đề (required)
  - Nội dung (required)
  - ID CLB (required)
  - Hình ảnh (file, tùy chọn)
  - Trạng thái (draft/published/archived)
  - Loại (post/announcement)
- **Output:**
  - Bài viết được tạo thành công
  - Redirect đến trang chi tiết bài viết
  - Thông báo lỗi validation nếu có

### UC-040: Xem chi tiết bài viết
**Mô tả chung:** Sinh viên/Admin xem nội dung chi tiết bài viết
- **Input:**
  - ID bài viết
- **Output:**
  - Nội dung đầy đủ bài viết
  - Thông tin tác giả, CLB
  - Danh sách bình luận
  - Form thêm bình luận (nếu đã đăng nhập)

### UC-041: Cập nhật bài viết
**Mô tả chung:** Tác giả/Admin cập nhật nội dung bài viết
- **Input:**
  - ID bài viết
  - Tiêu đề (tùy chọn)
  - Nội dung (tùy chọn)
  - Hình ảnh mới (file, tùy chọn)
  - Trạng thái (tùy chọn)
- **Output:**
  - Bài viết được cập nhật thành công
  - Redirect đến trang chi tiết bài viết
  - Thông báo lỗi nếu có

### UC-042: Xóa bài viết
**Mô tả chung:** Tác giả/Admin xóa bài viết (soft delete)
- **Input:**
  - ID bài viết
- **Output:**
  - Bài viết bị xóa thành công
  - Redirect đến danh sách bài viết
  - Thông báo thành công

### UC-043: Khôi phục bài viết
**Mô tả chung:** Admin khôi phục bài viết đã bị xóa
- **Input:**
  - ID bài viết
- **Output:**
  - Bài viết được khôi phục thành công
  - Redirect đến danh sách bài viết
  - Thông báo thành công

### UC-044: Cập nhật trạng thái bài viết
**Mô tả chung:** Admin thay đổi trạng thái bài viết (draft/published/archived)
- **Input:**
  - ID bài viết
  - Trạng thái mới
- **Output:**
  - Trạng thái được cập nhật thành công
  - Redirect đến danh sách bài viết
  - Thông báo thành công

### UC-045: Thêm bình luận vào bài viết
**Mô tả chung:** Sinh viên thêm bình luận vào bài viết
- **Input:**
  - ID bài viết
  - Nội dung bình luận (required)
- **Output:**
  - Bình luận được thêm thành công
  - Redirect đến trang chi tiết bài viết
  - Thông báo lỗi nếu có

### UC-046: Xóa bình luận
**Mô tả chung:** Tác giả bình luận/Admin xóa bình luận
- **Input:**
  - ID bình luận
  - Loại (post/event)
- **Output:**
  - Bình luận bị xóa thành công
  - Redirect đến trang chi tiết bài viết/sự kiện
  - Thông báo thành công

### UC-047: Đánh dấu đã xem thông báo
**Mô tả chung:** Sinh viên đánh dấu đã xem thông báo (announcement)
- **Input:**
  - ID bài viết (loại announcement)
- **Output:**
  - Trạng thái đã xem được cập nhật
  - Thông báo thành công (JSON response)

---

## 7. QUẢN LÝ QUỸ VÀ TÀI CHÍNH

### UC-048: Xem danh sách quỹ
**Mô tả chung:** Admin xem danh sách tất cả quỹ trong hệ thống
- **Input:**
  - Request GET /admin/funds
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách quỹ (phân trang)
  - Thông tin: Tên, CLB, Số tiền ban đầu, Số tiền hiện tại, Trạng thái

### UC-049: Tạo quỹ mới
**Mô tả chung:** Admin tạo quỹ mới cho CLB hoặc quỹ chung
- **Input:**
  - Tên quỹ (tùy chọn, tự động tạo nếu có CLB)
  - Mô tả (tùy chọn)
  - Số tiền ban đầu (required, numeric, min:0)
  - ID CLB (tùy chọn, exists:clubs,id)
  - Nguồn tiền (tùy chọn)
- **Output:**
  - Quỹ được tạo thành công
  - Số tiền hiện tại = Số tiền ban đầu
  - Redirect đến danh sách quỹ
  - Thông báo lỗi validation nếu có

### UC-050: Xem chi tiết quỹ
**Mô tả chung:** Admin xem thông tin chi tiết quỹ và lịch sử giao dịch
- **Input:**
  - ID quỹ
  - Tham số refresh (tùy chọn) để cập nhật số tiền hiện tại
- **Output:**
  - Thông tin đầy đủ quỹ
  - Thống kê: Tổng thu, Tổng chi, Số giao dịch chờ duyệt
  - Lịch sử giao dịch gần đây
  - Số tiền hiện tại (được cập nhật nếu có refresh)

### UC-051: Cập nhật thông tin quỹ
**Mô tả chung:** Admin cập nhật thông tin quỹ
- **Input:**
  - ID quỹ
  - Tên (required)
  - Mô tả (tùy chọn)
  - Nguồn tiền (tùy chọn)
  - Trạng thái (active/inactive/closed, required)
  - ID CLB (tùy chọn)
- **Output:**
  - Thông tin được cập nhật thành công
  - Redirect đến trang chi tiết quỹ
  - Thông báo lỗi nếu có

### UC-052: Xóa quỹ
**Mô tả chung:** Admin xóa quỹ (chỉ khi không có giao dịch)
- **Input:**
  - ID quỹ
- **Output:**
  - Quỹ bị xóa thành công
  - Redirect đến danh sách quỹ
  - Thông báo lỗi nếu quỹ có giao dịch

---

## 8. QUẢN LÝ GIAO DỊCH QUỸ

### UC-053: Xem danh sách giao dịch quỹ
**Mô tả chung:** Admin/Sinh viên xem danh sách giao dịch quỹ
- **Input:**
  - Request GET /admin/funds/{fund}/transactions hoặc /student/club-management/fund-transactions
  - ID quỹ (cho admin)
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo loại (income/expense, tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
- **Output:**
  - Danh sách giao dịch (phân trang)
  - Thông tin: Tiêu đề, Loại, Số tiền, Ngày giao dịch, Trạng thái, Người tạo

### UC-054: Tạo giao dịch quỹ mới
**Mô tả chung:** Admin/Sinh viên tạo giao dịch thu/chi quỹ
- **Input:**
  - ID quỹ (cho admin) hoặc ID CLB (cho sinh viên)
  - Loại (income/expense, required)
  - Tiêu đề (required)
  - Mô tả (tùy chọn)
  - Số tiền (required, numeric, min:0)
  - Ngày giao dịch (required, date)
  - Nguồn/Đích (tùy chọn)
  - ID sự kiện (tùy chọn)
  - Hóa đơn/chứng từ (file, tùy chọn)
- **Output:**
  - Giao dịch được tạo thành công với trạng thái 'pending'
  - Redirect đến danh sách giao dịch
  - Thông báo lỗi validation nếu có

### UC-055: Xem chi tiết giao dịch quỹ
**Mô tả chung:** Admin/Sinh viên xem thông tin chi tiết giao dịch
- **Input:**
  - ID giao dịch
- **Output:**
  - Thông tin đầy đủ giao dịch
  - Thông tin người tạo, người duyệt
  - Hóa đơn/chứng từ (nếu có)
  - Lịch sử phê duyệt

### UC-056: Cập nhật giao dịch quỹ
**Mô tả chung:** Người tạo/Admin cập nhật giao dịch (chỉ khi chưa được duyệt)
- **Input:**
  - ID giao dịch
  - Tiêu đề (tùy chọn)
  - Mô tả (tùy chọn)
  - Số tiền (tùy chọn)
  - Ngày giao dịch (tùy chọn)
  - Hóa đơn mới (file, tùy chọn)
- **Output:**
  - Giao dịch được cập nhật thành công
  - Redirect đến trang chi tiết giao dịch
  - Thông báo lỗi nếu đã được duyệt

### UC-057: Duyệt giao dịch quỹ
**Mô tả chung:** Admin/Chủ CLB duyệt giao dịch quỹ
- **Input:**
  - ID giao dịch
  - Ghi chú duyệt (tùy chọn)
- **Output:**
  - Trạng thái giao dịch chuyển thành 'approved'
  - Số tiền quỹ được cập nhật tự động (cộng nếu income, trừ nếu expense)
  - Redirect đến trang chi tiết giao dịch
  - Thông báo thành công

### UC-058: Duyệt một phần giao dịch quỹ
**Mô tả chung:** Admin duyệt một phần số tiền giao dịch
- **Input:**
  - ID giao dịch
  - Số tiền được duyệt (required, numeric, min:0, max:amount)
  - Ghi chú duyệt (tùy chọn)
- **Output:**
  - Trạng thái giao dịch chuyển thành 'partially_approved'
  - Số tiền quỹ được cập nhật theo số tiền được duyệt
  - Redirect đến trang chi tiết giao dịch
  - Thông báo thành công

### UC-059: Từ chối giao dịch quỹ
**Mô tả chung:** Admin/Chủ CLB từ chối giao dịch quỹ
- **Input:**
  - ID giao dịch
  - Lý do từ chối (required)
- **Output:**
  - Trạng thái giao dịch chuyển thành 'rejected'
  - Redirect đến trang chi tiết giao dịch
  - Thông báo thành công

### UC-060: Hủy giao dịch quỹ
**Mô tả chung:** Người tạo/Admin hủy giao dịch (chỉ khi chưa được duyệt)
- **Input:**
  - ID giao dịch
- **Output:**
  - Trạng thái giao dịch chuyển thành 'cancelled'
  - Redirect đến danh sách giao dịch
  - Thông báo thành công

### UC-061: Xóa giao dịch quỹ
**Mô tả chung:** Admin xóa giao dịch (chỉ khi chưa được duyệt)
- **Input:**
  - ID giao dịch
- **Output:**
  - Giao dịch bị xóa thành công
  - Redirect đến danh sách giao dịch
  - Thông báo lỗi nếu đã được duyệt

### UC-062: Xuất hóa đơn giao dịch
**Mô tả chung:** Admin xuất hóa đơn PDF cho giao dịch
- **Input:**
  - ID giao dịch
- **Output:**
  - File PDF hóa đơn được tải xuống
  - Thông tin: Tiêu đề, Số tiền, Ngày, Người tạo, Trạng thái

---

## 9. QUẢN LÝ YÊU CẦU CẤP KINH PHÍ

### UC-063: Xem danh sách yêu cầu cấp kinh phí
**Mô tả chung:** Admin/Sinh viên xem danh sách yêu cầu cấp kinh phí
- **Input:**
  - Request GET /admin/fund-requests hoặc /student/club-management/fund-requests
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo trạng thái (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
- **Output:**
  - Danh sách yêu cầu (phân trang)
  - Thông tin: Tiêu đề, CLB, Sự kiện, Số tiền yêu cầu, Số tiền được duyệt, Trạng thái

### UC-064: Tạo yêu cầu cấp kinh phí
**Mô tả chung:** Sinh viên/Admin tạo yêu cầu cấp kinh phí cho sự kiện
- **Input:**
  - Tiêu đề (required)
  - Mô tả (required)
  - Số tiền yêu cầu (required, numeric, min:0)
  - ID sự kiện (required, exists:events,id)
  - ID CLB (required, exists:clubs,id)
  - Danh sách hạng mục chi tiêu (array, tùy chọn)
    - Tên hạng mục (required_with)
    - Số tiền (required_with, numeric, min:0)
  - Tài liệu hỗ trợ (file, tùy chọn)
- **Output:**
  - Yêu cầu được tạo thành công với trạng thái 'pending'
  - Redirect đến danh sách yêu cầu
  - Thông báo lỗi validation nếu có

### UC-065: Xem chi tiết yêu cầu cấp kinh phí
**Mô tả chung:** Admin/Sinh viên xem thông tin chi tiết yêu cầu
- **Input:**
  - ID yêu cầu
- **Output:**
  - Thông tin đầy đủ yêu cầu
  - Thông tin sự kiện, CLB
  - Danh sách hạng mục chi tiêu
  - Tài liệu hỗ trợ (nếu có)
  - Lịch sử phê duyệt

### UC-066: Cập nhật yêu cầu cấp kinh phí
**Mô tả chung:** Người tạo cập nhật yêu cầu (chỉ khi chưa được duyệt)
- **Input:**
  - ID yêu cầu
  - Tiêu đề (tùy chọn)
  - Mô tả (tùy chọn)
  - Số tiền yêu cầu (tùy chọn)
  - Danh sách hạng mục chi tiêu (tùy chọn)
  - Tài liệu hỗ trợ mới (file, tùy chọn)
- **Output:**
  - Yêu cầu được cập nhật thành công
  - Redirect đến trang chi tiết yêu cầu
  - Thông báo lỗi nếu đã được duyệt

### UC-067: Duyệt yêu cầu cấp kinh phí
**Mô tả chung:** Admin duyệt yêu cầu cấp kinh phí
- **Input:**
  - ID yêu cầu
  - Số tiền được duyệt (required, numeric, min:0, max:requested_amount)
  - Ghi chú duyệt (tùy chọn)
- **Output:**
  - Trạng thái yêu cầu chuyển thành 'approved' hoặc 'partially_approved'
  - Tự động tạo giao dịch thu tiền vào quỹ CLB (nếu có quỹ)
  - Tự động tạo quỹ mới cho CLB nếu chưa có
  - Số dư quỹ được cập nhật tự động
  - Redirect đến trang chi tiết yêu cầu
  - Thông báo thành công

### UC-068: Từ chối yêu cầu cấp kinh phí
**Mô tả chung:** Admin từ chối yêu cầu cấp kinh phí
- **Input:**
  - ID yêu cầu
  - Lý do từ chối (required)
- **Output:**
  - Trạng thái yêu cầu chuyển thành 'rejected'
  - Redirect đến trang chi tiết yêu cầu
  - Thông báo thành công

### UC-069: Xóa yêu cầu cấp kinh phí
**Mô tả chung:** Người tạo xóa yêu cầu (chỉ khi chưa được duyệt)
- **Input:**
  - ID yêu cầu
- **Output:**
  - Yêu cầu bị xóa thành công
  - Tài liệu hỗ trợ bị xóa khỏi server
  - Redirect đến danh sách yêu cầu
  - Thông báo lỗi nếu đã được duyệt

### UC-070: Duyệt hàng loạt yêu cầu cấp kinh phí
**Mô tả chung:** Admin duyệt nhiều yêu cầu cùng lúc
- **Input:**
  - Tổng số tiền được duyệt (required, numeric, min:0)
  - Ghi chú duyệt (tùy chọn)
  - Danh sách yêu cầu (array, required, min:1)
    - ID yêu cầu (required, exists:fund_requests,id)
    - Số tiền được duyệt cho từng yêu cầu (required, numeric, min:0)
- **Output:**
  - Số lượng yêu cầu được duyệt
  - Số lượng yêu cầu bị từ chối
  - Tất cả yêu cầu được duyệt tự động tạo giao dịch vào quỹ CLB
  - Redirect đến danh sách yêu cầu
  - Thông báo lỗi nếu tổng số tiền phân bổ vượt quá số tiền duyệt

---

## 10. QUẢN LÝ QUYẾT TOÁN KINH PHÍ

### UC-071: Xem danh sách yêu cầu cần quyết toán
**Mô tả chung:** Admin xem danh sách yêu cầu đã được duyệt cần quyết toán
- **Input:**
  - Request GET /admin/fund-settlements
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
- **Output:**
  - Danh sách yêu cầu chờ quyết toán (phân trang)
  - Danh sách yêu cầu đã quyết toán (phân trang)
  - Thông tin: Tiêu đề, CLB, Số tiền được duyệt, Trạng thái quyết toán

### UC-072: Tạo quyết toán kinh phí
**Mô tả chung:** Admin tạo quyết toán cho yêu cầu đã được duyệt
- **Input:**
  - ID yêu cầu cấp kinh phí
  - Số tiền thực tế chi (required, numeric, min:0)
  - Ghi chú quyết toán (tùy chọn)
  - Hóa đơn/chứng từ quyết toán (file, tùy chọn, bắt buộc nếu chi vượt dự toán)
- **Output:**
  - Quyết toán được tạo thành công
  - Trạng thái quyết toán chuyển thành 'settled'
  - Tự động tạo giao dịch chi tiêu thực tế vào quỹ CLB
  - Số dư quỹ được cập nhật (trừ số tiền thực tế chi)
  - Xử lý tiền thừa/thiếu tự động
  - Redirect đến danh sách quyết toán
  - Thông báo lỗi nếu số tiền vượt quá mà không có hóa đơn

### UC-073: Xem chi tiết quyết toán
**Mô tả chung:** Admin xem thông tin chi tiết quyết toán
- **Input:**
  - ID yêu cầu cấp kinh phí
- **Output:**
  - Thông tin đầy đủ quyết toán
  - So sánh số tiền được duyệt và số tiền thực tế
  - Hóa đơn/chứng từ quyết toán
  - Ghi chú quyết toán
  - Thông tin người quyết toán

---

## 11. QUẢN LÝ PHÂN QUYỀN

### UC-074: Xem danh sách quyền hạn
**Mô tả chung:** Admin xem danh sách tất cả quyền hạn trong hệ thống
- **Input:**
  - Request GET /admin/permissions hoặc /admin/permissions-detailed
- **Output:**
  - Danh sách quyền hạn
  - Thông tin: Tên quyền, Mô tả, Số người dùng có quyền

### UC-075: Xem quyền hạn của người dùng
**Mô tả chung:** Admin xem quyền hạn của người dùng trong từng CLB
- **Input:**
  - ID người dùng
  - ID CLB (tùy chọn)
- **Output:**
  - Danh sách quyền hạn của người dùng
  - Phân loại theo CLB
  - Vai trò trong từng CLB

### UC-076: Cập nhật quyền hạn người dùng
**Mô tả chung:** Admin cập nhật quyền hạn của người dùng trong CLB
- **Input:**
  - ID người dùng
  - ID CLB
  - Danh sách quyền (array of permission IDs)
- **Output:**
  - Quyền hạn được cập nhật thành công
  - Redirect đến trang quản lý phân quyền
  - Thông báo thành công

### UC-077: Thêm quyền cho CLB
**Mô tả chung:** Admin thêm quyền mặc định cho CLB
- **Input:**
  - ID CLB
  - Danh sách quyền (array of permission IDs)
- **Output:**
  - Quyền được thêm thành công
  - Tất cả thành viên có vai trò phù hợp được cấp quyền
  - Thông báo thành công

---

## 12. QUẢN LÝ TÀI NGUYÊN CLB

### UC-078: Xem danh sách tài nguyên CLB
**Mô tả chung:** Admin xem danh sách tài nguyên (tài liệu, file) của CLB
- **Input:**
  - Request GET /admin/club-resources
  - Tham số tìm kiếm (tùy chọn)
  - Tham số lọc theo CLB (tùy chọn)
- **Output:**
  - Danh sách tài nguyên (phân trang)
  - Thông tin: Tên, CLB, Loại file, Kích thước, Ngày upload

### UC-079: Tải lên tài nguyên CLB
**Mô tả chung:** Admin tải lên tài liệu/tài nguyên cho CLB
- **Input:**
  - Tên tài nguyên (required)
  - Mô tả (tùy chọn)
  - ID CLB (required)
  - File (required, file)
  - Loại (document/image/video/other, tùy chọn)
- **Output:**
  - Tài nguyên được tải lên thành công
  - Redirect đến danh sách tài nguyên
  - Thông báo lỗi nếu file không hợp lệ

### UC-080: Tải xuống tài nguyên CLB
**Mô tả chung:** Admin/Sinh viên tải xuống tài nguyên CLB
- **Input:**
  - ID tài nguyên
- **Output:**
  - File được tải xuống
  - Thông báo lỗi nếu file không tồn tại

### UC-081: Xóa tài nguyên CLB
**Mô tả chung:** Admin xóa tài nguyên (soft delete)
- **Input:**
  - ID tài nguyên
- **Output:**
  - Tài nguyên bị xóa thành công
  - File bị xóa khỏi server
  - Redirect đến danh sách tài nguyên
  - Thông báo thành công

### UC-082: Khôi phục tài nguyên CLB
**Mô tả chung:** Admin khôi phục tài nguyên đã bị xóa
- **Input:**
  - ID tài nguyên
- **Output:**
  - Tài nguyên được khôi phục thành công
  - Redirect đến danh sách tài nguyên
  - Thông báo thành công

---

## 13. QUẢN LÝ THÔNG BÁO

### UC-083: Xem danh sách thông báo
**Mô tả chung:** Sinh viên/Admin xem danh sách thông báo
- **Input:**
  - Request GET /student/notifications hoặc /admin/notifications
- **Output:**
  - Danh sách thông báo (phân trang)
  - Thông tin: Tiêu đề, Nội dung, Loại, Ngày tạo, Trạng thái đọc

### UC-084: Xem chi tiết thông báo
**Mô tả chung:** Sinh viên/Admin xem nội dung chi tiết thông báo
- **Input:**
  - ID thông báo
- **Output:**
  - Nội dung đầy đủ thông báo
  - Trạng thái chuyển thành 'đã đọc'
  - Thông tin người gửi, thời gian

### UC-085: Đánh dấu tất cả thông báo đã đọc
**Mô tả chung:** Sinh viên/Admin đánh dấu tất cả thông báo là đã đọc
- **Input:**
  - Request POST /admin/notifications/mark-all-read
- **Output:**
  - Tất cả thông báo được đánh dấu đã đọc
  - Thông báo thành công (JSON response)

---

## 14. QUẢN LÝ HỒ SƠ

### UC-086: Xem hồ sơ cá nhân
**Mô tả chung:** Sinh viên xem thông tin hồ sơ cá nhân
- **Input:**
  - Request GET /student/profile
- **Output:**
  - Thông tin đầy đủ hồ sơ: Tên, Email, Mã sinh viên, Avatar
  - Danh sách CLB tham gia
  - Số lượng sự kiện đã tham gia
  - Số lượng bài viết đã đăng

### UC-087: Cập nhật hồ sơ cá nhân
**Mô tả chung:** Sinh viên cập nhật thông tin hồ sơ
- **Input:**
  - Tên (tùy chọn)
  - Email (tùy chọn, unique)
  - Avatar (file, tùy chọn)
  - Mật khẩu mới (tùy chọn)
  - Xác nhận mật khẩu (required_with:password)
- **Output:**
  - Hồ sơ được cập nhật thành công
  - Redirect đến trang hồ sơ
  - Thông báo lỗi validation nếu có

---

## 15. QUẢN LÝ CÀI ĐẶT CLB

### UC-088: Xem cài đặt CLB
**Mô tả chung:** Chủ CLB/Ban cán sự xem và chỉnh sửa cài đặt CLB
- **Input:**
  - ID câu lạc bộ
- **Output:**
  - Form cài đặt CLB
  - Thông tin hiện tại: Tên, Mô tả, Logo, Trạng thái

### UC-089: Cập nhật cài đặt CLB
**Mô tả chung:** Chủ CLB/Ban cán sự cập nhật cài đặt CLB
- **Input:**
  - ID câu lạc bộ
  - Tên (tùy chọn)
  - Mô tả (tùy chọn)
  - Logo (file, tùy chọn)
- **Output:**
  - Cài đặt được cập nhật thành công
  - Redirect đến trang cài đặt CLB
  - Thông báo lỗi nếu có

---

## 16. QUẢN LÝ BÁO CÁO

### UC-090: Xem báo cáo CLB
**Mô tả chung:** Chủ CLB/Ban cán sự xem báo cáo hoạt động CLB
- **Input:**
  - ID câu lạc bộ
  - Tham số khoảng thời gian (tùy chọn)
- **Output:**
  - Thống kê: Số thành viên, Số sự kiện, Số bài viết
  - Biểu đồ hoạt động theo thời gian
  - Danh sách sự kiện gần đây
  - Thống kê tài chính (nếu có quyền)

---

## 17. QUẢN LÝ THÙNG RÁC

### UC-091: Xem thùng rác
**Mô tả chung:** Admin xem danh sách các bản ghi đã bị xóa (soft delete)
- **Input:**
  - Request GET /admin/trash
  - Tham số loại (users/clubs/posts/events, tùy chọn)
- **Output:**
  - Danh sách bản ghi đã xóa (phân trang)
  - Thông tin: Tên, Loại, Ngày xóa, Người xóa

### UC-092: Khôi phục bản ghi
**Mô tả chung:** Admin khôi phục bản ghi từ thùng rác
- **Input:**
  - ID bản ghi
  - Loại bản ghi
- **Output:**
  - Bản ghi được khôi phục thành công
  - Redirect đến danh sách thùng rác
  - Thông báo thành công

### UC-093: Xóa vĩnh viễn bản ghi
**Mô tả chung:** Admin xóa vĩnh viễn bản ghi khỏi hệ thống
- **Input:**
  - ID bản ghi
  - Loại bản ghi
- **Output:**
  - Bản ghi bị xóa vĩnh viễn thành công
  - Redirect đến danh sách thùng rác
  - Thông báo thành công

### UC-094: Khôi phục tất cả
**Mô tả chung:** Admin khôi phục tất cả bản ghi trong thùng rác
- **Input:**
  - Loại bản ghi (tùy chọn)
- **Output:**
  - Tất cả bản ghi được khôi phục thành công
  - Redirect đến danh sách thùng rác
  - Thông báo thành công

### UC-095: Xóa vĩnh viễn tất cả
**Mô tả chung:** Admin xóa vĩnh viễn tất cả bản ghi trong thùng rác
- **Input:**
  - Loại bản ghi (tùy chọn)
- **Output:**
  - Tất cả bản ghi bị xóa vĩnh viễn thành công
  - Redirect đến danh sách thùng rác
  - Thông báo cảnh báo và xác nhận

---

## 18. DASHBOARD VÀ THỐNG KÊ

### UC-096: Xem dashboard Admin
**Mô tả chung:** Admin xem tổng quan hệ thống
- **Input:**
  - Request GET /admin/dashboard
  - Tham số lọc thời gian (today/yesterday/week/month/year/custom, tùy chọn)
- **Output:**
  - Thống kê tổng quan: Tổng người dùng, Tổng CLB, Tổng sự kiện, Tổng bài viết
  - Thống kê tăng trưởng theo khoảng thời gian
  - Danh sách người dùng mới
  - Danh sách CLB mới
  - Danh sách sự kiện gần đây
  - Top 5 CLB hoạt động mạnh nhất
  - Thống kê theo lĩnh vực

### UC-097: Xem dashboard Sinh viên
**Mô tả chung:** Sinh viên xem tổng quan hoạt động cá nhân
- **Input:**
  - Request GET /student/dashboard
- **Output:**
  - Số CLB đang tham gia
  - Số sự kiện đã đăng ký
  - Số bài viết đã đăng
  - Thông báo mới
  - Sự kiện sắp tới
  - Bài viết mới từ CLB

---

## 19. TÌM KIẾM

### UC-098: Tìm kiếm toàn hệ thống
**Mô tả chung:** Admin tìm kiếm thông tin trong hệ thống
- **Input:**
  - Request GET /admin/search
  - Từ khóa tìm kiếm (required)
  - Loại (users/clubs/events/posts, tùy chọn)
- **Output:**
  - Kết quả tìm kiếm (phân trang)
  - Phân loại theo loại: Người dùng, CLB, Sự kiện, Bài viết
  - Thông tin chi tiết từng kết quả

---

## 20. TRANG CHỦ

### UC-099: Xem trang chủ
**Mô tả chung:** Người dùng (chưa đăng nhập/đã đăng nhập) xem trang chủ
- **Input:**
  - Request GET /
  - Tham số tìm kiếm CLB (tùy chọn)
  - Tham số lọc theo lĩnh vực (tùy chọn)
  - Tham số sắp xếp (tùy chọn)
- **Output:**
  - Thống kê tổng quan: Số CLB, Số thành viên, Số sự kiện, Số bài viết
  - Danh sách CLB nổi bật
  - Danh sách sự kiện sắp tới
  - Danh sách bài viết mới nhất
  - Form tìm kiếm CLB

---

## TỔNG KẾT

Hệ thống quản lý câu lạc bộ sinh viên bao gồm **99 Use Case** chính, được phân loại thành **20 nhóm chức năng**:

1. **Quản lý xác thực và phân quyền** (4 UC)
2. **Quản lý người dùng** (6 UC)
3. **Quản lý câu lạc bộ** (10 UC)
4. **Quản lý thành viên CLB** (7 UC)
5. **Quản lý sự kiện** (10 UC)
6. **Quản lý bài viết** (10 UC)
7. **Quản lý quỹ và tài chính** (5 UC)
8. **Quản lý giao dịch quỹ** (10 UC)
9. **Quản lý yêu cầu cấp kinh phí** (8 UC)
10. **Quản lý quyết toán kinh phí** (3 UC)
11. **Quản lý phân quyền** (4 UC)
12. **Quản lý tài nguyên CLB** (5 UC)
13. **Quản lý thông báo** (3 UC)
14. **Quản lý hồ sơ** (2 UC)
15. **Quản lý cài đặt CLB** (2 UC)
16. **Quản lý báo cáo** (1 UC)
17. **Quản lý thùng rác** (5 UC)
18. **Dashboard và thống kê** (2 UC)
19. **Tìm kiếm** (1 UC)
20. **Trang chủ** (1 UC)

---

## CHI TIẾT CÁC BẢNG TRONG DATABASE

### 1. BẢNG users

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 255 | Yes | - | Tên người dùng |
| 3 | email | varchar | 255 | Yes | UNIQUE | Email đăng nhập |
| 4 | email_verified_at | timestamp | - | No | - | Thời gian xác thực email |
| 5 | password | varchar | 255 | Yes | - | Mật khẩu (hashed) |
| 6 | phone | varchar | 20 | No | - | Số điện thoại |
| 7 | address | text | - | No | - | Địa chỉ |
| 8 | avatar | varchar | 255 | No | - | Đường dẫn ảnh đại diện |
| 9 | is_admin | boolean | - | Yes | - | Là admin (default: false) |
| 10 | role | varchar | 50 | Yes | INDEX | Vai trò: user, club_manager, admin |
| 11 | student_id | varchar | 20 | No | - | Mã sinh viên |
| 12 | last_online | timestamp | - | No | - | Thời gian online cuối cùng |
| 13 | remember_token | varchar | 100 | No | - | Token nhớ đăng nhập |
| 14 | created_at | timestamp | - | No | - | Thời gian tạo |
| 15 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 16 | deleted_at | timestamp | - | No | - | Soft delete |

### 2. BẢNG fields

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 100 | Yes | - | Tên lĩnh vực |
| 3 | slug | varchar | 255 | Yes | UNIQUE | Slug URL |
| 4 | description | text | - | Yes | - | Mô tả lĩnh vực |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 3. BẢNG permissions

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 100 | Yes | - | Tên quyền |
| 3 | description | text | - | Yes | - | Mô tả quyền |
| 4 | created_at | timestamp | - | No | - | Thời gian tạo |
| 5 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 4. BẢNG clubs

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 255 | Yes | - | Tên câu lạc bộ |
| 3 | slug | varchar | 255 | Yes | UNIQUE | Slug URL |
| 4 | description | text | - | Yes | - | Mô tả CLB |
| 5 | logo | varchar | 255 | Yes | - | Đường dẫn logo |
| 6 | field_id | bigint unsigned | - | Yes | FK | Lĩnh vực (FK: fields.id) |
| 7 | owner_id | bigint unsigned | - | Yes | FK | Chủ CLB (FK: users.id) |
| 8 | leader_id | bigint unsigned | - | No | FK | Trưởng CLB (FK: users.id) |
| 9 | max_members | int | - | Yes | - | Số thành viên tối đa (default: 50) |
| 10 | status | enum | - | Yes | - | Trạng thái: pending, approved, rejected, active, inactive |
| 11 | rejection_reason | text | - | No | - | Lý do từ chối |
| 12 | deletion_reason | text | - | No | - | Lý do xóa |
| 13 | created_at | timestamp | - | No | - | Thời gian tạo |
| 14 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 15 | deleted_at | timestamp | - | No | - | Soft delete |

### 5. BẢNG club_members

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 3 | user_id | bigint unsigned | - | Yes | FK | Thành viên (FK: users.id) |
| 4 | role_in_club | enum | - | Yes | - | Vai trò: chunhiem, phonhiem, thanhvien |
| 5 | position | enum | - | Yes | - | Vị trí: leader, vice_president, officer, member |
| 6 | status | enum | - | Yes | - | Trạng thái: active, pending, inactive, approved, rejected |
| 7 | joined_at | timestamp | - | No | - | Thời gian tham gia |
| 8 | left_at | timestamp | - | No | - | Thời gian rời CLB |
| 9 | left_reason | text | - | No | - | Lý do rời CLB |
| 10 | created_at | timestamp | - | No | - | Thời gian tạo |
| 11 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 12 | deleted_at | timestamp | - | No | - | Soft delete |

### 6. BẢNG club_join_requests

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | user_id | bigint unsigned | - | Yes | FK | Người xin tham gia (FK: users.id) |
| 3 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 4 | message | text | - | No | - | Lời nhắn |
| 5 | status | enum | - | Yes | - | Trạng thái: pending, approved, rejected |
| 6 | reviewed_by | bigint unsigned | - | No | FK | Người duyệt (FK: users.id) |
| 7 | reviewed_at | timestamp | - | No | - | Thời gian duyệt |
| 8 | created_at | timestamp | - | No | - | Thời gian tạo |
| 9 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 10 | UNIQUE(user_id, club_id, status) | - | - | - | UNIQUE | Một user chỉ có một đơn pending cho một CLB |

### 7. BẢNG departments

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 3 | name | varchar | 255 | Yes | - | Tên ban |
| 4 | description | text | - | No | - | Mô tả ban |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 7 | deleted_at | timestamp | - | No | - | Soft delete |

### 8. BẢNG events

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 3 | created_by | bigint unsigned | - | Yes | FK | Người tạo (FK: users.id) |
| 4 | title | varchar | 255 | Yes | - | Tiêu đề sự kiện |
| 5 | slug | varchar | 255 | Yes | UNIQUE | Slug URL |
| 6 | description | text | - | Yes | - | Mô tả sự kiện |
| 7 | image | varchar | 255 | No | - | Hình ảnh sự kiện |
| 8 | start_time | datetime | - | Yes | - | Thời gian bắt đầu |
| 9 | end_time | datetime | - | Yes | - | Thời gian kết thúc |
| 10 | registration_deadline | datetime | - | No | - | Hạn chót đăng ký |
| 11 | mode | varchar | 16 | Yes | - | Chế độ: public, private, offline, online, hybrid |
| 12 | location | varchar | 255 | No | - | Địa điểm tổ chức |
| 13 | max_participants | int | - | No | - | Số người tham gia tối đa |
| 14 | status | varchar | 16 | Yes | - | Trạng thái: pending, approved, rejected, active, canceled, completed |
| 15 | cancellation_reason | text | - | No | - | Lý do hủy |
| 16 | cancelled_at | timestamp | - | No | - | Thời gian hủy |
| 17 | main_organizer | varchar | 255 | No | - | Người phụ trách chính |
| 18 | organizing_team | text | - | No | - | Ban tổ chức |
| 19 | co_organizers | text | - | No | - | Đơn vị phối hợp |
| 20 | contact_info | text | - | No | - | Thông tin liên hệ (JSON) |
| 21 | proposal_file | varchar | 500 | No | - | File đề xuất |
| 22 | poster_file | varchar | 500 | No | - | File poster |
| 23 | permit_file | varchar | 500 | No | - | File giấy phép |
| 24 | guests | text | - | No | - | Danh sách khách mời |
| 25 | created_at | timestamp | - | No | - | Thời gian tạo |
| 26 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 27 | deleted_at | timestamp | - | No | - | Soft delete |

### 9. BẢNG event_registrations

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | user_id | bigint unsigned | - | Yes | FK | Người đăng ký (FK: users.id) |
| 3 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 4 | status | enum | - | Yes | - | Trạng thái: registered, pending, approved, rejected, attended, absent, canceled |
| 5 | joined_at | timestamp | - | No | - | Thời gian tham gia |
| 6 | left_at | timestamp | - | No | - | Thời gian rời |
| 7 | created_at | timestamp | - | No | - | Thời gian tạo |
| 8 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 9 | deleted_at | timestamp | - | No | - | Soft delete |

### 10. BẢNG event_member_evaluations

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 3 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 4 | evaluator_id | bigint unsigned | - | Yes | FK | Người đánh giá (FK: users.id) |
| 5 | member_id | bigint unsigned | - | Yes | FK | Thành viên được đánh giá (FK: users.id) |
| 6 | score | int | - | Yes | - | Điểm đánh giá |
| 7 | comment | text | - | No | - | Nhận xét |
| 8 | created_at | timestamp | - | No | - | Thời gian tạo |
| 9 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 11. BẢNG event_images

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 3 | image_path | varchar | 255 | Yes | - | Đường dẫn ảnh |
| 4 | alt_text | varchar | 255 | No | - | Mô tả ảnh |
| 5 | sort_order | int | - | Yes | - | Thứ tự sắp xếp (default: 0) |
| 6 | created_at | timestamp | - | No | - | Thời gian tạo |
| 7 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 12. BẢNG event_comments

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | user_id | bigint unsigned | - | Yes | FK | Người bình luận (FK: users.id) |
| 3 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 4 | parent_id | bigint unsigned | - | No | FK | Bình luận cha (FK: event_comments.id) |
| 5 | content | text | - | Yes | - | Nội dung bình luận |
| 6 | status | enum | - | Yes | - | Trạng thái: visible, hidden, deleted |
| 7 | deletion_reason | text | - | No | - | Lý do xóa |
| 8 | deleted_at | timestamp | - | No | - | Thời gian xóa |
| 9 | created_at | timestamp | - | No | - | Thời gian tạo |
| 10 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 13. BẢNG event_logs

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 3 | user_id | bigint unsigned | - | Yes | FK | Người thực hiện (FK: users.id) |
| 4 | action | enum | - | Yes | - | Hành động: created, updated, approved, rejected, canceled, completed |
| 5 | reason | text | - | No | - | Lý do |
| 6 | created_at | timestamp | - | No | - | Thời gian tạo |
| 7 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 14. BẢNG posts

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 3 | user_id | bigint unsigned | - | Yes | FK | Tác giả (FK: users.id) |
| 4 | title | varchar | 255 | Yes | - | Tiêu đề bài viết |
| 5 | slug | varchar | 255 | Yes | UNIQUE | Slug URL |
| 6 | content | longtext | - | Yes | - | Nội dung bài viết |
| 7 | image | varchar | 255 | No | - | Hình ảnh bài viết |
| 8 | views | bigint unsigned | - | Yes | - | Số lượt xem (default: 0) |
| 9 | type | enum | - | No | - | Loại: post, announcement, document |
| 10 | status | varchar | 20 | Yes | - | Trạng thái: published, hidden, deleted, members_only |
| 11 | created_at | timestamp | - | No | - | Thời gian tạo |
| 12 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 13 | deleted_at | timestamp | - | No | - | Soft delete |

### 15. BẢNG post_attachments

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | post_id | bigint unsigned | - | Yes | FK | Bài viết (FK: posts.id) |
| 3 | file_url | varchar | 255 | No | - | Đường dẫn file |
| 4 | file_type | enum | - | Yes | - | Loại file: image, video, document, other |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 16. BẢNG post_comments

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | user_id | bigint unsigned | - | Yes | FK | Người bình luận (FK: users.id) |
| 3 | post_id | bigint unsigned | - | Yes | FK | Bài viết (FK: posts.id) |
| 4 | parent_id | bigint unsigned | - | No | FK | Bình luận cha (FK: post_comments.id) |
| 5 | content | text | - | Yes | - | Nội dung bình luận |
| 6 | status | enum | - | Yes | - | Trạng thái: visible, hidden, deleted |
| 7 | deletion_reason | text | - | No | - | Lý do xóa |
| 8 | deleted_at | timestamp | - | No | - | Thời gian xóa |
| 9 | created_at | timestamp | - | No | - | Thời gian tạo |
| 10 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 17. BẢNG notifications

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | sender_id | bigint unsigned | - | Yes | FK | Người gửi (FK: users.id) |
| 3 | type | varchar | 50 | No | - | Loại thông báo (default: general) |
| 4 | related_id | bigint unsigned | - | No | - | ID đối tượng liên quan |
| 5 | related_type | varchar | 50 | No | - | Loại đối tượng liên quan |
| 6 | title | varchar | 255 | No | - | Tiêu đề thông báo |
| 7 | message | text | - | Yes | - | Nội dung thông báo |
| 8 | read_at | timestamp | - | No | - | Thời gian đọc |
| 9 | created_at | timestamp | - | No | - | Thời gian tạo |
| 10 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 11 | deleted_at | timestamp | - | No | - | Soft delete |

### 18. BẢNG notification_targets

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | notification_id | bigint unsigned | - | Yes | FK | Thông báo (FK: notifications.id) |
| 3 | target_type | enum | - | Yes | - | Loại đối tượng: all, club, user |
| 4 | target_id | bigint unsigned | - | No | - | ID đối tượng nhận |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 19. BẢNG notification_reads

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | notification_id | bigint unsigned | - | Yes | FK | Thông báo (FK: notifications.id) |
| 3 | user_id | bigint unsigned | - | Yes | FK | Người đọc (FK: users.id) |
| 4 | is_read | boolean | - | Yes | - | Đã đọc (default: false) |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 7 | deleted_at | timestamp | - | No | - | Soft delete |

### 20. BẢNG user_permissions_club

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | user_id | bigint unsigned | - | Yes | FK | Người dùng (FK: users.id) |
| 3 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 4 | permission_id | bigint unsigned | - | Yes | FK | Quyền (FK: permissions.id) |
| 5 | created_at | timestamp | - | No | - | Thời gian tạo |
| 6 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 21. BẢNG funds

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 255 | No | - | Tên quỹ |
| 3 | description | text | - | No | - | Mô tả quỹ |
| 4 | initial_amount | decimal | 15,2 | Yes | - | Số tiền ban đầu (default: 0) |
| 5 | current_amount | decimal | 15,2 | Yes | - | Số tiền hiện tại (default: 0) |
| 6 | status | enum | - | Yes | - | Trạng thái: active, inactive, closed |
| 7 | club_id | bigint unsigned | - | No | FK | CLB (FK: clubs.id) |
| 8 | created_by | bigint unsigned | - | Yes | FK | Người tạo (FK: users.id) |
| 9 | voucher_path | varchar | 255 | No | - | Đường dẫn voucher |
| 10 | approval_status | enum | - | Yes | - | Trạng thái duyệt: pending, approved, rejected |
| 11 | approved_by | bigint unsigned | - | No | FK | Người duyệt (FK: users.id) |
| 12 | approved_at | timestamp | - | No | - | Thời gian duyệt |
| 13 | approved_amount | decimal | 15,2 | No | - | Số tiền được duyệt |
| 14 | approval_note | text | - | No | - | Ghi chú duyệt |
| 15 | created_at | timestamp | - | No | - | Thời gian tạo |
| 16 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 22. BẢNG fund_transactions

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | fund_id | bigint unsigned | - | Yes | FK | Quỹ (FK: funds.id) |
| 3 | type | enum | - | Yes | - | Loại: income, expense |
| 4 | transaction_type | enum | - | No | - | Loại giao dịch: event_expense, operational_expense, income, settlement, refund |
| 5 | source | varchar | 255 | No | - | Nguồn tiền |
| 6 | amount | decimal | 15,2 | Yes | - | Số tiền |
| 7 | title | varchar | 255 | Yes | - | Tiêu đề giao dịch |
| 8 | description | text | - | No | - | Mô tả |
| 9 | category | varchar | 255 | No | - | Danh mục |
| 10 | expense_category_id | bigint unsigned | - | No | FK | Danh mục chi tiêu (FK: expense_categories.id) |
| 11 | transaction_date | date | - | Yes | - | Ngày giao dịch |
| 12 | status | enum | - | Yes | - | Trạng thái: pending, approved, rejected, cancelled |
| 13 | rejection_reason | text | - | No | - | Lý do từ chối |
| 14 | receipt_path | varchar | 255 | No | - | Đường dẫn hóa đơn |
| 15 | receipt_paths | json | - | No | - | Danh sách đường dẫn hóa đơn (JSON) |
| 16 | created_by | bigint unsigned | - | Yes | FK | Người tạo (FK: users.id) |
| 17 | approved_by | bigint unsigned | - | No | FK | Người duyệt (FK: users.id) |
| 18 | approved_at | timestamp | - | No | - | Thời gian duyệt |
| 19 | event_id | bigint unsigned | - | No | FK | Sự kiện (FK: events.id) |
| 20 | created_at | timestamp | - | No | - | Thời gian tạo |
| 21 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 23. BẢNG fund_transaction_items

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | transaction_id | bigint unsigned | - | Yes | FK | Giao dịch (FK: fund_transactions.id) |
| 3 | item_name | varchar | 255 | Yes | - | Tên khoản mục |
| 4 | amount | decimal | 15,2 | Yes | - | Số tiền khoản mục |
| 5 | status | varchar | 20 | Yes | - | Trạng thái: approved, rejected (default: approved) |
| 6 | rejection_reason | text | - | No | - | Lý do từ chối |
| 7 | notes | text | - | No | - | Ghi chú |
| 8 | created_at | timestamp | - | No | - | Thời gian tạo |
| 9 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 24. BẢNG fund_requests

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | title | varchar | 255 | Yes | - | Tiêu đề yêu cầu |
| 3 | description | text | - | Yes | - | Mô tả chi tiết |
| 4 | requested_amount | decimal | 15,2 | Yes | - | Số tiền xin cấp |
| 5 | event_id | bigint unsigned | - | Yes | FK | Sự kiện (FK: events.id) |
| 6 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 7 | status | enum | - | Yes | - | Trạng thái: pending, approved, rejected, partially_approved |
| 8 | settlement_status | enum | - | Yes | - | Trạng thái quyết toán: pending, settlement_pending, settled, cancelled |
| 9 | settlement_notes | text | - | No | - | Ghi chú quyết toán |
| 10 | settlement_documents | json | - | No | - | Tài liệu quyết toán (JSON) |
| 11 | actual_amount | decimal | 15,2 | No | - | Số tiền thực tế chi |
| 12 | settlement_date | timestamp | - | No | - | Ngày quyết toán |
| 13 | settled_by | bigint unsigned | - | No | FK | Người quyết toán (FK: users.id) |
| 14 | approved_amount | decimal | 15,2 | No | - | Số tiền được duyệt |
| 15 | rejection_reason | text | - | No | - | Lý do từ chối |
| 16 | approval_notes | text | - | No | - | Ghi chú duyệt |
| 17 | created_by | bigint unsigned | - | Yes | FK | Người tạo (FK: users.id) |
| 18 | approved_by | bigint unsigned | - | No | FK | Người duyệt (FK: users.id) |
| 19 | approved_at | timestamp | - | No | - | Thời gian duyệt |
| 20 | expense_items | json | - | No | - | Danh sách các mục chi tiêu (JSON) |
| 21 | supporting_documents | varchar | 255 | No | - | Tài liệu hỗ trợ |
| 22 | created_at | timestamp | - | No | - | Thời gian tạo |
| 23 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 25. BẢNG fund_items

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | fund_id | bigint unsigned | - | Yes | FK | Quỹ (FK: funds.id) |
| 3 | description | varchar | 255 | Yes | - | Mô tả |
| 4 | amount | decimal | 15,2 | Yes | - | Số tiền |
| 5 | status | enum | - | Yes | - | Trạng thái: pending, approved, rejected |
| 6 | rejection_reason | text | - | No | - | Lý do từ chối |
| 7 | created_at | timestamp | - | No | - | Thời gian tạo |
| 8 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 26. BẢNG expense_categories

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | name | varchar | 100 | Yes | - | Tên khoản mục |
| 3 | description | text | - | No | - | Mô tả |
| 4 | icon | varchar | 50 | No | - | Icon FontAwesome |
| 5 | color | varchar | 20 | Yes | - | Màu hiển thị (default: #007bff) |
| 6 | is_active | boolean | - | Yes | - | Đang hoạt động (default: true) |
| 7 | display_order | int | - | Yes | - | Thứ tự hiển thị (default: 0) |
| 8 | created_at | timestamp | - | No | - | Thời gian tạo |
| 9 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 27. BẢNG club_resources

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | title | varchar | 255 | Yes | - | Tiêu đề tài nguyên |
| 3 | slug | varchar | 255 | Yes | UNIQUE | Slug URL |
| 4 | description | longtext | - | No | - | Mô tả |
| 5 | resource_type | enum | - | Yes | - | Loại: document, video, template, reference, link, other |
| 6 | club_id | bigint unsigned | - | Yes | FK | CLB (FK: clubs.id) |
| 7 | user_id | bigint unsigned | - | Yes | FK | Người tạo (FK: users.id) |
| 8 | file_path | varchar | 255 | No | - | Đường dẫn file |
| 9 | file_name | varchar | 255 | No | - | Tên file |
| 10 | file_type | varchar | 255 | No | - | Loại file |
| 11 | file_size | bigint | - | No | - | Kích thước file (bytes) |
| 12 | thumbnail_path | varchar | 255 | No | - | Đường dẫn thumbnail |
| 13 | external_link | varchar | 255 | No | - | Liên kết ngoài |
| 14 | tags | json | - | No | - | Thẻ (JSON) |
| 15 | status | enum | - | Yes | - | Trạng thái: active, inactive, archived |
| 16 | view_count | int | - | Yes | - | Số lượt xem (default: 0) |
| 17 | download_count | int | - | Yes | - | Số lượt tải (default: 0) |
| 18 | created_at | timestamp | - | No | - | Thời gian tạo |
| 19 | updated_at | timestamp | - | No | - | Thời gian cập nhật |
| 20 | deleted_at | timestamp | - | No | - | Soft delete |

### 28. BẢNG club_resource_images

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_resource_id | bigint unsigned | - | Yes | FK | Tài nguyên (FK: club_resources.id) |
| 3 | image_path | varchar | 255 | Yes | - | Đường dẫn ảnh |
| 4 | image_name | varchar | 255 | Yes | - | Tên ảnh |
| 5 | image_type | varchar | 255 | Yes | - | Loại ảnh |
| 6 | image_size | bigint | - | Yes | - | Kích thước ảnh (bytes) |
| 7 | thumbnail_path | varchar | 255 | No | - | Đường dẫn thumbnail |
| 8 | sort_order | int | - | Yes | - | Thứ tự sắp xếp (default: 0) |
| 9 | is_primary | boolean | - | Yes | - | Ảnh chính (default: false) |
| 10 | created_at | timestamp | - | No | - | Thời gian tạo |
| 11 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

### 29. BẢNG club_resource_files

| No. | Name | Type | Length | Not null | Key | Ghi chú |
|-----|------|------|--------|----------|-----|---------|
| 1 | id | bigint unsigned | - | Yes | PK, AI | Khóa chính |
| 2 | club_resource_id | bigint unsigned | - | Yes | FK | Tài nguyên (FK: club_resources.id) |
| 3 | file_path | varchar | 255 | Yes | - | Đường dẫn file |
| 4 | file_name | varchar | 255 | Yes | - | Tên file |
| 5 | file_type | varchar | 255 | Yes | - | Loại file |
| 6 | file_size | bigint | - | Yes | - | Kích thước file (bytes) |
| 7 | thumbnail_path | varchar | 255 | No | - | Đường dẫn thumbnail |
| 8 | sort_order | int | - | Yes | - | Thứ tự sắp xếp (default: 0) |
| 9 | is_primary | boolean | - | Yes | - | File chính (default: false) |
| 10 | created_at | timestamp | - | No | - | Thời gian tạo |
| 11 | updated_at | timestamp | - | No | - | Thời gian cập nhật |

---

**TỔNG KẾT:** Hệ thống có **29 bảng** trong database, bao gồm các bảng chính về quản lý người dùng, câu lạc bộ, sự kiện, bài viết, quỹ và tài chính, thông báo, và các bảng hỗ trợ khác.





