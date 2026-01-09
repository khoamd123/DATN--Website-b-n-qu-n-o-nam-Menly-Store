@extends('layouts.student')

@section('title', 'Chỉnh sửa sự kiện - UniClubs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <!-- Back Button -->
            <div class="mb-3">
                <a href="{{ route('student.events.show', $event->id) }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại chi tiết sự kiện
                </a>
            </div>

            <!-- Page Header -->
            <div class="mb-4">
                <h2 class="mb-2">
                    <i class="fas fa-calendar-edit text-teal me-2"></i>Chỉnh sửa sự kiện
                </h2>
                <p class="text-muted mb-0">Sự kiện: <strong>{{ $event->title }}</strong> - CLB: <strong>{{ $userClub->name }}</strong></p>
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

            <form method="POST" action="{{ route('student.events.update', $event->id) }}" enctype="multipart/form-data" id="eventEditForm">
                @csrf
                @method('PUT')
                
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
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $event->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Câu lạc bộ</label>
                            <input type="text" class="form-control" value="{{ $userClub->name }}" disabled>
                            <small class="text-muted">Không thể thay đổi CLB</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mô tả sự kiện</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="6" placeholder="Mô tả chi tiết về sự kiện..." id="description">{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" name="start_time" value="{{ old('start_time', $event->start_time ? $event->start_time->format('Y-m-d\TH:i') : '') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" name="end_time" value="{{ old('end_time', $event->end_time ? $event->end_time->format('Y-m-d\TH:i') : '') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Chế độ <span class="text-danger">*</span></label>
                            <select class="form-select @error('mode') is-invalid @enderror" name="mode" required>
                                <option value="offline" {{ old('mode', $event->mode) == 'offline' ? 'selected' : '' }}>Tại chỗ</option>
                                <option value="online" {{ old('mode', $event->mode) == 'online' ? 'selected' : '' }}>Trực tuyến</option>
                                <option value="hybrid" {{ old('mode', $event->mode) == 'hybrid' ? 'selected' : '' }}>Kết hợp</option>
                            </select>
                            @error('mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Địa điểm</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" name="location" value="{{ old('location', $event->location) }}" placeholder="Địa điểm tổ chức sự kiện">
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
                            <input type="number" class="form-control @error('max_participants') is-invalid @enderror" name="max_participants" value="{{ old('max_participants', $event->max_participants) }}" placeholder="Số người tham gia tối đa" min="1">
                            @error('max_participants')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Để trống nếu không giới hạn</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Hạn chót đăng ký tham gia</label>
                            <input type="datetime-local" class="form-control @error('registration_deadline') is-invalid @enderror" name="registration_deadline" value="{{ old('registration_deadline', $event->registration_deadline ? $event->registration_deadline->format('Y-m-d\TH:i') : '') }}">
                            @error('registration_deadline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Phải trước thời gian bắt đầu sự kiện</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label">Chế độ hiển thị <span class="text-danger">*</span></label>
                            <select class="form-select @error('visibility') is-invalid @enderror" name="visibility" id="visibilitySelect" required onchange="updateVisibilityDescription()">
                                <option value="public" {{ old('visibility', $event->visibility ?? 'public') == 'public' ? 'selected' : '' }}>Công khai</option>
                                <option value="internal" {{ old('visibility', $event->visibility ?? 'public') == 'internal' ? 'selected' : '' }}>Chỉ nội bộ CLB</option>
                            </select>
                            @error('visibility')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="visibilityDescription">
                                @if(($event->visibility ?? 'public') === 'internal')
                                    Chỉ nội bộ CLB: Chỉ thành viên của {{ $userClub->name ?? 'CLB' }} mới có thể xem sự kiện này.
                                @else
                                    Công khai: Tất cả mọi người có thể xem sự kiện này.
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3 text-teal"><i class="fas fa-users me-2"></i> Thông tin tổ chức</h5>

                <div class="mb-3">
                    <label class="form-label">Người phụ trách chính <span class="text-muted">(Chủ nhiệm / Trưởng ban tổ chức)</span></label>
                    <input type="text" class="form-control @error('main_organizer') is-invalid @enderror" name="main_organizer" value="{{ old('main_organizer', $event->main_organizer) }}" placeholder="Nhập tên người phụ trách chính">
                    @error('main_organizer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Ban tổ chức / Đội ngũ thực hiện</label>
                    <textarea class="form-control @error('organizing_team') is-invalid @enderror" name="organizing_team" rows="3" placeholder="Nhập danh sách ban tổ chức, mỗi người một dòng hoặc cách nhau bởi dấu phẩy">{{ old('organizing_team', $event->organizing_team) }}</textarea>
                    @error('organizing_team')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Đơn vị phối hợp hoặc đồng tổ chức <span class="text-muted">(nếu có)</span></label>
                    <textarea class="form-control @error('co_organizers') is-invalid @enderror" name="co_organizers" rows="2" placeholder="Nhập tên các đơn vị phối hợp">{{ old('co_organizers', $event->co_organizers) }}</textarea>
                    @error('co_organizers')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại liên hệ</label>
                            @php
                                $contact = $event->contact_info ?? [];
                                $contactPhone = old('contact_phone', is_array($contact) ? ($contact['phone'] ?? '') : '');
                            @endphp
                            <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" name="contact_phone" value="{{ $contactPhone }}" placeholder="VD: 0123456789">
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email liên hệ</label>
                            @php
                                $contactEmail = old('contact_email', is_array($contact) ? ($contact['email'] ?? '') : '');
                            @endphp
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" name="contact_email" value="{{ $contactEmail }}" placeholder="VD: contact@example.com">
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                <h5 class="mb-3 text-teal"><i class="fas fa-file me-2"></i> Tài liệu và File</h5>

                <div class="mb-3">
                    <label class="form-label">Kế hoạch chi tiết <span class="text-muted">(Proposal / Plan file)</span></label>
                    @if($event->proposal_file)
                        <div class="mb-2">
                            <small class="text-muted">File hiện tại: </small>
                            <a href="{{ asset('storage/' . $event->proposal_file) }}" target="_blank" class="text-primary">
                                <i class="fas fa-file-pdf me-1"></i>Xem file
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('proposal_file') is-invalid @enderror" name="proposal_file" accept=".pdf,.doc,.docx">
                    @error('proposal_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Định dạng: PDF, DOC, DOCX. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Poster / Ấn phẩm truyền thông <span class="text-muted">(nếu có)</span></label>
                    @if($event->poster_file)
                        <div class="mb-2">
                            <small class="text-muted">File hiện tại: </small>
                            <a href="{{ asset('storage/' . $event->poster_file) }}" target="_blank" class="text-primary">
                                <i class="fas fa-file-image me-1"></i>Xem file
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('poster_file') is-invalid @enderror" name="poster_file" accept=".pdf,.jpg,.jpeg,.png">
                    @error('poster_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Định dạng: PDF, JPG, JPEG, PNG. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Giấy phép / Công văn xin tổ chức <span class="text-muted">(nếu cần)</span></label>
                    @if($event->permit_file)
                        <div class="mb-2">
                            <small class="text-muted">File hiện tại: </small>
                            <a href="{{ asset('storage/' . $event->permit_file) }}" target="_blank" class="text-primary">
                                <i class="fas fa-file-alt me-1"></i>Xem file
                            </a>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('permit_file') is-invalid @enderror" name="permit_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    @error('permit_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Định dạng: PDF, DOC, DOCX, JPG, JPEG, PNG. Tối đa 10MB. Để trống nếu không thay đổi.</small>
                </div>

                <hr class="my-4">
                <h5 class="mb-3 text-teal"><i class="fas fa-user-tie me-2"></i> Khách mời</h5>

                <div class="mb-3">
                    <label class="form-label">Loại khách mời</label>
                    @php
                        $guests = $event->guests ?? [];
                        $guestTypes = old('guest_types', is_array($guests) && isset($guests['types']) ? $guests['types'] : []);
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
                    @error('guest_types')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="guest_other_info_wrapper" style="display: {{ in_array('other', $guestTypes) ? 'block' : 'none' }};">
                    <label class="form-label">Thông tin khách mời (Khác) <span class="text-danger">*</span></label>
                    @php
                        $guestOtherInfo = old('guest_other_info', is_array($guests) && isset($guests['other_info']) ? $guests['other_info'] : '');
                    @endphp
                    <textarea class="form-control @error('guest_other_info') is-invalid @enderror" name="guest_other_info" id="guest_other_info" rows="4" placeholder="Nhập thông tin chi tiết về khách mời khác...">{{ $guestOtherInfo }}</textarea>
                    @error('guest_other_info')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Vui lòng nhập thông tin chi tiết về khách mời khi chọn "Khác..."</small>
                </div>

                <hr class="my-4">

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Lưu ý quan trọng:</strong> Sau khi chỉnh sửa và gửi, sự kiện sẽ được chuyển về trạng thái <strong>"Chờ duyệt"</strong> và cần được quản trị viên duyệt lại trước khi hiển thị công khai.
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                    <a href="{{ route('student.events.show', $event->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Sử dụng CKEditor từ CDN đã được load trong layout
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ClassicEditor !== 'undefined') {
        const textarea = document.querySelector('#description');
        if (textarea) {
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
                .catch(error => {
                    console.error('Error creating CKEditor:', error);
                });
        }
    }
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
        });

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
        } else {
            wrapper.style.display = 'none';
            textarea.removeAttribute('required');
        }
    }
}

// Cập nhật mô tả chế độ hiển thị
function updateVisibilityDescription() {
    const select = document.getElementById('visibilitySelect');
    const description = document.getElementById('visibilityDescription');
    const clubName = '{{ $userClub->name ?? "CLB" }}';
    
    if (select && description) {
        if (select.value === 'internal') {
            description.textContent = `Chỉ nội bộ CLB: Chỉ thành viên của ${clubName} mới có thể xem sự kiện này.`;
        } else {
            description.textContent = 'Công khai: Tất cả mọi người có thể xem sự kiện này.';
        }
    }
}

// Khởi tạo mô tả khi trang load
document.addEventListener('DOMContentLoaded', function() {
    updateVisibilityDescription();
});

// Validate form trước khi submit
document.getElementById('eventEditForm')?.addEventListener('submit', function(e) {
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
    
    col.remove();
    
    const input = document.getElementById('imagesInput');
    const dataTransfer = new DataTransfer();
    
    Array.from(input.files).forEach((file, index) => {
        if (index !== imageIndex) {
            dataTransfer.items.add(file);
        }
    });
    
    input.files = dataTransfer.files;
    
    const container = document.getElementById('imagesPreviewContainer');
    if (container.children.length === 0) {
        document.getElementById('imagesPreviewWrap').style.display = 'none';
    } else {
        Array.from(container.children).forEach((col, index) => {
            col.setAttribute('data-file-index', index);
        });
    }
}
</script>
@endpush
@endsection

