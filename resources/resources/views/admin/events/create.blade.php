@extends('admin.layouts.app')

@section('title', 'Tạo sự kiện mới')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-calendar-plus"></i> Tạo sự kiện mới</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.plans-schedule') }}">Kế hoạch</a></li>
                <li class="breadcrumb-item active">Tạo sự kiện</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin sự kiện</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" id="eventCreateForm">
                        @csrf
                        
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Có lỗi xảy ra:</h6>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sự kiện <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('club_id') is-invalid @enderror" name="club_id" required>
                                        <option value="">Chọn câu lạc bộ</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả sự kiện</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="4" placeholder="Mô tả chi tiết về sự kiện..." id="description">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ảnh sự kiện</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple id="imagesInput">
                            <small class="text-muted">Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 5MB mỗi ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
                            <div class="mt-2" id="imagesPreviewWrap" style="display:none;">
                                <div class="row" id="imagesPreviewContainer">
                                    <!-- Ảnh preview sẽ được thêm vào đây -->
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chế độ</label>
                                    <select class="form-select @error('mode') is-invalid @enderror" name="mode">
                                        <option value="offline" {{ old('mode') == 'offline' ? 'selected' : '' }}>Tại chỗ</option>
                                        <option value="online" {{ old('mode') == 'online' ? 'selected' : '' }}>Trực tuyến</option>
                                        <option value="hybrid" {{ old('mode') == 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                                    </select>
                                    @error('mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Địa điểm</label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location') }}" placeholder="Địa điểm tổ chức sự kiện">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng tối đa</label>
                                    <input type="number" class="form-control @error('max_participants') is-invalid @enderror" name="max_participants" value="{{ old('max_participants') }}" placeholder="Số người tham gia tối đa">
                                    @error('max_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hạn chót đăng ký tham gia</label>
                                    <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" name="registration_deadline" value="{{ old('registration_deadline') }}">
                                    @error('registration_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Phải trước thời gian bắt đầu sự kiện</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-users"></i> Thông tin tổ chức</h5>

                        <div class="mb-3">
                            <label class="form-label">Người phụ trách chính <span class="text-muted">(Chủ nhiệm / Trưởng ban tổ chức)</span></label>
                            <input type="text" class="form-control @error('main_organizer') is-invalid @enderror" name="main_organizer" value="{{ old('main_organizer') }}" placeholder="Nhập tên người phụ trách chính">
                            @error('main_organizer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ban tổ chức / Đội ngũ thực hiện</label>
                            <textarea class="form-control @error('organizing_team') is-invalid @enderror" name="organizing_team" rows="3" placeholder="Nhập danh sách ban tổ chức, mỗi người một dòng hoặc cách nhau bởi dấu phẩy">{{ old('organizing_team') }}</textarea>
                            @error('organizing_team')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đơn vị phối hợp hoặc đồng tổ chức <span class="text-muted">(nếu có)</span></label>
                            <textarea class="form-control @error('co_organizers') is-invalid @enderror" name="co_organizers" rows="2" placeholder="Nhập tên các đơn vị phối hợp">{{ old('co_organizers') }}</textarea>
                            @error('co_organizers')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại liên hệ</label>
                                    <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="VD: 0123456789">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email liên hệ</label>
                                    <input type="email" class="form-control @error('contact_email') is-invalid @enderror" name="contact_email" value="{{ old('contact_email') }}" placeholder="VD: contact@example.com">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-file"></i> Tài liệu và File</h5>

                        <div class="mb-3">
                            <label class="form-label">Kế hoạch chi tiết <span class="text-muted">(Proposal / Plan file)</span></label>
                            <input type="file" class="form-control @error('proposal_file') is-invalid @enderror" name="proposal_file" accept=".pdf,.doc,.docx">
                            @error('proposal_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Định dạng: PDF, DOC, DOCX. Tối đa 10MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Poster / Ấn phẩm truyền thông <span class="text-muted">(nếu có)</span></label>
                            <input type="file" class="form-control @error('poster_file') is-invalid @enderror" name="poster_file" accept=".pdf,.jpg,.jpeg,.png">
                            @error('poster_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Định dạng: PDF, JPG, JPEG, PNG. Tối đa 10MB</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giấy phép / Công văn xin tổ chức <span class="text-muted">(nếu cần)</span></label>
                            <input type="file" class="form-control @error('permit_file') is-invalid @enderror" name="permit_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            @error('permit_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Định dạng: PDF, DOC, DOCX, JPG, JPEG, PNG. Tối đa 10MB</small>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-user-tie"></i> Khách mời</h5>

                        <div class="mb-3">
                            <label class="form-label">Loại khách mời</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="lecturer" id="guest_lecturer" {{ is_array(old('guest_types')) && in_array('lecturer', old('guest_types')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_lecturer">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Giảng viên
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="student" id="guest_student" {{ is_array(old('guest_types')) && in_array('student', old('guest_types')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_student">
                                            <i class="fas fa-user-graduate me-2"></i>Sinh viên
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="sponsor" id="guest_sponsor" {{ is_array(old('guest_types')) && in_array('sponsor', old('guest_types')) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_sponsor">
                                            <i class="fas fa-hand-holding-usd me-2"></i>Nhà tài trợ
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="other" id="guest_other" {{ is_array(old('guest_types')) && in_array('other', old('guest_types')) ? 'checked' : '' }} onchange="toggleGuestOtherInfo()">
                                        <label class="form-check-label" for="guest_other">
                                            <i class="fas fa-ellipsis-h me-2"></i>Khác...
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('guest_types')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="guest_other_info_wrapper" style="display: none;">
                            <label class="form-label">Thông tin khách mời (Khác) <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('guest_other_info') is-invalid @enderror" name="guest_other_info" id="guest_other_info" rows="4" placeholder="Nhập thông tin chi tiết về khách mời khác...">{{ old('guest_other_info') }}</textarea>
                            @error('guest_other_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Vui lòng nhập thông tin chi tiết về khách mời khi chọn "Khác..."</small>
                        </div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo sự kiện
                            </button>
                            <a href="{{ route('admin.plans-schedule') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
// Sử dụng CKEditor từ CDN đã được load trong layout
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    // Kiểm tra ClassicEditor
    console.log('ClassicEditor available:', typeof ClassicEditor);
    
    if (typeof ClassicEditor === 'undefined') {
        console.error('ClassicEditor is not loaded!');
        return;
    }
    
    // Tìm textarea
    const textarea = document.querySelector('#description');
    console.log('Textarea found:', textarea);
    
    if (!textarea) {
        console.error('Textarea with id "description" not found');
        return;
    }
    
    console.log('Creating CKEditor instance...');
    
    ClassicEditor
        .create(textarea, {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'link', 'blockQuote', '|',
                    'undo', 'redo'
                ]
            }
        })
        .then(editor => {
            console.log('✅ CKEditor created successfully!', editor);
        })
        .catch(error => {
            console.error('❌ Error creating CKEditor:', error);
        });
});

// Preview images
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('imagesInput');
    const wrap = document.getElementById('imagesPreviewWrap');
    const container = document.getElementById('imagesPreviewContainer');

    input?.addEventListener('change', function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) {
            wrap.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        container.innerHTML = '';

        // Tạo DataTransfer để quản lý files
        const dataTransfer = new DataTransfer();
        Array.from(files).forEach(file => dataTransfer.items.add(file));

        // Hiển thị preview
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-2';
                col.setAttribute('data-file-index', index);
                col.innerHTML = `
                    <div style="position: relative; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                        <img src="${ev.target.result}" alt="Preview ${index + 1}" style="width: 100%; height: 150px; object-fit: cover; display: block;">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeImage(this)" style="position: absolute; top: 5px; right: 5px; z-index: 10; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                container.appendChild(col);
            };
            reader.readAsDataURL(file);
            col.dataset.fileName = file.name;
        });

        // Cập nhật input với files mới
        input.files = dataTransfer.files;

        wrap.style.display = 'block';
    });

    // Khởi tạo trạng thái textarea khách mời khác
    toggleGuestOtherInfo();
    
    // Khôi phục trạng thái nếu có lỗi validation
    const guestOtherCheckbox = document.getElementById('guest_other');
    if (guestOtherCheckbox && guestOtherCheckbox.checked) {
        toggleGuestOtherInfo();
    }
});

function toggleGuestOtherInfo() {
    const checkbox = document.getElementById('guest_other');
    const wrapper = document.getElementById('guest_other_info_wrapper');
    const textarea = document.getElementById('guest_other_info');
    
    if (checkbox && wrapper && textarea) {
        if (checkbox.checked) {
            wrapper.style.display = 'block';
            textarea.setAttribute('required', 'required');
            // Scroll đến textarea để người dùng dễ nhìn thấy
            setTimeout(() => {
                textarea.focus();
            }, 100);
        } else {
            wrapper.style.display = 'none';
            textarea.removeAttribute('required');
            textarea.value = '';
        }
    }
}

// Validate form trước khi submit
document.getElementById('eventCreateForm')?.addEventListener('submit', function(e) {
    const checkbox = document.getElementById('guest_other');
    const textarea = document.getElementById('guest_other_info');
    
    if (checkbox && checkbox.checked && textarea) {
        if (!textarea.value.trim()) {
            e.preventDefault();
            alert('Vui lòng nhập thông tin khách mời khi chọn "Khác..."');
            textarea.focus();
            return false;
        }
    }
});

// Hàm xóa ảnh
function removeImage(button) {
    const col = button.closest('.col-md-4');
    const imageIndex = parseInt(col.getAttribute('data-file-index'));
    
    // Xóa preview
    col.remove();
    
    // Lấy input file hiện tại
    const input = document.getElementById('imagesInput');
    const dataTransfer = new DataTransfer();
    
    // Copy tất cả files trừ file bị xóa
    Array.from(input.files).forEach((file, index) => {
        if (index !== imageIndex) {
            dataTransfer.items.add(file);
        }
    });
    
    // Cập nhật input files
    input.files = dataTransfer.files;
    
    // Nếu không còn ảnh nào thì ẩn preview container
    const container = document.getElementById('imagesPreviewContainer');
    if (container.children.length === 0) {
        document.getElementById('imagesPreviewWrap').style.display = 'none';
    } else {
        // Re-index các ảnh còn lại
        Array.from(container.children).forEach((col, index) => {
            col.setAttribute('data-file-index', index);
        });
    }
}
</script>
@endpush
</div>
@endsection
