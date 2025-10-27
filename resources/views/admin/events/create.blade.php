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
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Tên sự kiện <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        <option value="">Chọn câu lạc bộ</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả sự kiện</label>
                            <textarea class="form-control" name="description" rows="4" placeholder="Mô tả chi tiết về sự kiện..." id="description"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ảnh sự kiện</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple id="imagesInput">
                            <small class="text-muted">Hỗ trợ: JPG, JPEG, PNG, WEBP. Tối đa 2MB mỗi ảnh. Có thể chọn nhiều ảnh cùng lúc.</small>
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
                                    <input type="datetime-local" class="form-control" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian kết thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control" name="end_time" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Chế độ</label>
                                    <select class="form-select" name="mode">
                                        <option value="offline">Tại chỗ</option>
                                        <option value="online">Trực tuyến</option>
                                        <option value="hybrid">Kết hợp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Địa điểm</label>
                                    <input type="text" class="form-control" name="location" placeholder="Địa điểm tổ chức sự kiện">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số lượng tối đa</label>
                                    <input type="number" class="form-control" name="max_participants" placeholder="Số người tham gia tối đa">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="status">
                                <option value="draft">Bản nháp</option>
                                <option value="pending">Chờ duyệt</option>
                                <option value="approved">Đã duyệt</option>
                                <option value="ongoing">Đang diễn ra</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
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
