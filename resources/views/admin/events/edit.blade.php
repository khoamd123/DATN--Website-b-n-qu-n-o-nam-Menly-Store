@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa sự kiện')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-calendar-edit"></i> Chỉnh sửa sự kiện</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.events.index') }}">Sự kiện</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
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
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin sự kiện</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.events.update', $event->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sự kiện <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" value="{{ old('title', $event->title) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ (old('club_id', $event->club_id) == $club->id) ? 'selected' : '' }}>{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả sự kiện</label>
                            <textarea class="form-control" name="description" id="description" rows="4" placeholder="Mô tả chi tiết về sự kiện...">{{ old('description', $event->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Ảnh sự kiện</label>
                            
                            @if($event->images->count() > 0)
                                <div class="mb-3">
                                    <h6>Ảnh hiện tại:</h6>
                                    <div class="row">
                                        @foreach($event->images as $image)
                                            <div class="col-md-3 mb-2">
                                                <div class="position-relative">
                                                    <img src="{{ $image->image_url }}" alt="{{ $image->alt_text }}" class="img-thumbnail" style="width: 100%; height: 120px; object-fit: cover;">
                                                    <div class="form-check position-absolute" style="top: 5px; left: 5px;">
                                                        <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_image_{{ $image->id }}">
                                                        <label class="form-check-label text-white" for="remove_image_{{ $image->id }}" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">
                                                            <i class="fas fa-trash"></i>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mb-2">
                                <label class="form-label">Thêm ảnh mới:</label>
                                <input type="file" class="form-control" name="images[]" accept="image/*" multiple id="imagesInput">
                                <small class="text-muted">Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 5MB mỗi ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
                            </div>
                            
                            <div class="mt-2" id="imagesPreviewWrap" style="display:none;">
                                <h6>Xem trước ảnh mới:</h6>
                                <div class="row" id="imagesPreviewContainer">
                                    <!-- Ảnh preview sẽ được thêm vào đây -->
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian bắt đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($event->start_time)->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($event->end_time)->format('Y-m-d\TH:i')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chế độ</label>
                                    <select class="form-select" name="mode">
                                        <option value="offline" {{ old('mode', $event->mode) === 'offline' ? 'selected' : '' }}>Tại chỗ</option>
                                        <option value="online" {{ old('mode', $event->mode) === 'online' ? 'selected' : '' }}>Trực tuyến</option>
                                        <option value="hybrid" {{ old('mode', $event->mode) === 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Địa điểm</label>
                                    <input type="text" class="form-control" name="location" value="{{ old('location', $event->location) }}" placeholder="Địa điểm tổ chức sự kiện">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng tối đa</label>
                                    <input type="number" class="form-control" name="max_participants" value="{{ old('max_participants', $event->max_participants) }}" placeholder="Số người tham gia tối đa">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Hạn chót đăng ký tham gia</label>
                                    <input type="datetime-local" class="form-control" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline ? \Carbon\Carbon::parse($event->registration_deadline)->format('Y-m-d\TH:i') : '') }}">
                                    <small class="text-muted">Phải trước thời gian bắt đầu sự kiện</small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-users"></i> Thông tin tổ chức</h5>

                        <div class="mb-3">
                            <label class="form-label">Người phụ trách chính <span class="text-muted">(Chủ nhiệm / Trưởng ban tổ chức)</span></label>
                            <input type="text" class="form-control" name="main_organizer" value="{{ old('main_organizer', $event->main_organizer) }}" placeholder="Nhập tên người phụ trách chính">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ban tổ chức / Đội ngũ thực hiện</label>
                            <textarea class="form-control" name="organizing_team" rows="3" placeholder="Nhập danh sách ban tổ chức, mỗi người một dòng hoặc cách nhau bởi dấu phẩy">{{ old('organizing_team', $event->organizing_team) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Đơn vị phối hợp hoặc đồng tổ chức <span class="text-muted">(nếu có)</span></label>
                            <textarea class="form-control" name="co_organizers" rows="2" placeholder="Nhập tên các đơn vị phối hợp">{{ old('co_organizers', $event->co_organizers) }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại liên hệ</label>
                                    <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', is_array($event->contact_info) ? ($event->contact_info['phone'] ?? '') : '') }}" placeholder="VD: 0123456789">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email liên hệ</label>
                                    <input type="email" class="form-control" name="contact_email" value="{{ old('contact_email', is_array($event->contact_info) ? ($event->contact_info['email'] ?? '') : '') }}" placeholder="VD: contact@example.com">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-file"></i> Tài liệu và File</h5>

                        <div class="mb-3">
                            <label class="form-label">Kế hoạch chi tiết <span class="text-muted">(Proposal / Plan file)</span></label>
                            @if($event->proposal_file)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $event->proposal_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file"></i> Xem file hiện tại
                                    </a>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="proposal_file" accept=".pdf,.doc,.docx">
                            <small class="text-muted">Định dạng: PDF, DOC, DOCX. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Poster / Ấn phẩm truyền thông <span class="text-muted">(nếu có)</span></label>
                            @if($event->poster_file)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file"></i> Xem file hiện tại
                                    </a>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="poster_file" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Định dạng: PDF, JPG, JPEG, PNG. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giấy phép / Công văn xin tổ chức <span class="text-muted">(nếu cần)</span></label>
                            @if($event->permit_file)
                                <div class="mb-2">
                                    <a href="{{ asset('storage/' . $event->permit_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file"></i> Xem file hiện tại
                                    </a>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="permit_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <small class="text-muted">Định dạng: PDF, DOC, DOCX, JPG, JPEG, PNG. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-user-tie"></i> Khách mời</h5>

                        <div class="mb-3">
                            <label class="form-label">Loại khách mời</label>
                            @php
                                $guestData = is_array($event->guests) ? $event->guests : (is_string($event->guests) ? json_decode($event->guests, true) : []);
                                $guestTypes = $guestData['types'] ?? [];
                                $guestOtherInfo = $guestData['other_info'] ?? '';
                            @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="lecturer" id="guest_lecturer" {{ in_array('lecturer', $guestTypes) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_lecturer">
                                            <i class="fas fa-chalkboard-teacher me-2"></i>Giảng viên
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="student" id="guest_student" {{ in_array('student', $guestTypes) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_student">
                                            <i class="fas fa-user-graduate me-2"></i>Sinh viên
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="sponsor" id="guest_sponsor" {{ in_array('sponsor', $guestTypes) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="guest_sponsor">
                                            <i class="fas fa-hand-holding-usd me-2"></i>Nhà tài trợ
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="guest_types[]" value="other" id="guest_other" {{ in_array('other', $guestTypes) ? 'checked' : '' }} onchange="toggleGuestOtherInfo()">
                                        <label class="form-check-label" for="guest_other">
                                            <i class="fas fa-ellipsis-h me-2"></i>Khác...
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="guest_other_info_wrapper" style="display: {{ in_array('other', $guestTypes) ? 'block' : 'none' }};">
                            <label class="form-label">Thông tin khách mời (Khác) <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="guest_other_info" id="guest_other_info" rows="4" placeholder="Nhập thông tin chi tiết về khách mời khác...">{{ old('guest_other_info', $guestOtherInfo) }}</textarea>
                            <small class="text-muted">Vui lòng nhập thông tin chi tiết về khách mời khi chọn "Khác..."</small>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <select class="form-select" name="status">
                                        <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                        <option value="pending" {{ old('status', $event->status) === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                        <option value="approved" {{ old('status', $event->status) === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="ongoing" {{ old('status', $event->status) === 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                        <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                        <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('admin.posts.upload-image'), 'csrfToken' => csrf_token()])
<script>
// Sử dụng CKEditor từ CDN đã được load trong layout
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Edit Page');
    
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
    
    // Tạo upload adapter plugin
    const SimpleUploadAdapterPlugin = window.CKEditorUploadAdapterFactory('{{ route("admin.posts.upload-image") }}', '{{ csrf_token() }}');
    
    ClassicEditor
        .create(textarea, {
            extraPlugins: [SimpleUploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'link', 'blockQuote', 'uploadImage', '|',
                    'undo', 'redo'
                ]
            },
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'toggleImageCaption',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side'
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
    
    let selectedFiles = [];

    if (!input) return;

    // Hàm render preview
    function renderPreview() {
        if (selectedFiles.length === 0) {
            wrap.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        container.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-2';
                col.innerHTML = `
                    <div style="position: relative; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                        <img src="${ev.target.result}" alt="Preview ${index + 1}" style="width: 100%; height: 120px; object-fit: cover; display: block;">
                        <div style="position: absolute; top: 5px; left: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                            Mới ${index + 1}
                        </div>
                        <button type="button" class="btn-remove-image" data-index="${index}" style="position: absolute; top: 5px; right: 5px; background: rgba(220,53,69,0.9); color: white; border: none; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; line-height: 1;" title="Xóa ảnh này">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                container.appendChild(col);
                
                // Thêm event listener cho nút xóa
                const removeBtn = col.querySelector('.btn-remove-image');
                removeBtn.addEventListener('click', function() {
                    const indexToRemove = parseInt(this.getAttribute('data-index'));
                    selectedFiles.splice(indexToRemove, 1);
                    
                    // Cập nhật lại file input
                    const dataTransfer = new DataTransfer();
                    selectedFiles.forEach(file => {
                        dataTransfer.items.add(file);
                    });
                    input.files = dataTransfer.files;
                    
                    // Render lại preview
                    renderPreview();
                });
            };
            reader.readAsDataURL(file);
        });

        wrap.style.display = 'block';
    }

    input.addEventListener('change', function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) {
            selectedFiles = [];
            renderPreview();
            return;
        }

        selectedFiles = Array.from(files);
        renderPreview();
    });

    // Khởi tạo trạng thái textarea khách mời khác
    toggleGuestOtherInfo();
});

function toggleGuestOtherInfo() {
    const checkbox = document.getElementById('guest_other');
    const wrapper = document.getElementById('guest_other_info_wrapper');
    const textarea = document.getElementById('guest_other_info');
    
    if (checkbox && wrapper && textarea) {
        if (checkbox.checked) {
            wrapper.style.display = 'block';
            textarea.required = true;
        } else {
            wrapper.style.display = 'none';
            textarea.required = false;
        }
    }
}
</script>
@endpush
@endsection


