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
        <div class="col-md-8">
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

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb"></i> Mẹo tạo sự kiện:</h6>
                        <ul class="mb-0">
                            <li>Đặt tên sự kiện rõ ràng và dễ hiểu</li>
                            <li>Mô tả chi tiết để thu hút người tham gia</li>
                            <li>Chọn thời gian phù hợp với đối tượng</li>
                            <li>Xác định địa điểm cụ thể nếu tổ chức offline</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Lưu ý:</h6>
                        <ul class="mb-0">
                            <li>Sự kiện sẽ được tạo ở trạng thái "Bản nháp"</li>
                            <li>Cần duyệt trước khi công khai</li>
                            <li>Kiểm tra kỹ thông tin trước khi lưu</li>
                        </ul>
                    </div>
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

        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-2';
                col.innerHTML = `
                    <div style="position: relative; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                        <img src="${ev.target.result}" alt="Preview ${index + 1}" style="width: 100%; height: 150px; object-fit: cover; display: block;">
                        <div style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                            ${index + 1}
                        </div>
                    </div>
                `;
                container.appendChild(col);
            };
            reader.readAsDataURL(file);
        });

        wrap.style.display = 'block';
    });
});
</script>
@endpush
</div>
@endsection
