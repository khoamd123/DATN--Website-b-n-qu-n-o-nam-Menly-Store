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
        <div class="col-md-8">
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
                                <small class="text-muted">Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 2MB mỗi ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
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

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Gợi ý</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="mb-1"><i class="fas fa-lightbulb"></i> Lưu ý khi chỉnh sửa</h6>
                        <ul class="mb-0">
                            <li>Ảnh mới sẽ thay thế ảnh hiện tại nếu tải lên.</li>
                            <li>Chọn "Xóa ảnh hiện tại" để bỏ ảnh bìa.</li>
                            <li>Đảm bảo thời gian kết thúc sau thời gian bắt đầu.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
});
</script>
@endpush
@endsection


