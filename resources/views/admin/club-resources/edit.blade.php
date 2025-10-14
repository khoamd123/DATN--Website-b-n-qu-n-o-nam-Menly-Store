@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa Tài nguyên - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">✏️ Chỉnh sửa Tài nguyên</h1>
        <p class="text-muted mb-0">Cập nhật thông tin tài nguyên CLB</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.club-resources.index') }}">Tài nguyên CLB</a></li>
            <li class="breadcrumb-item active">Chỉnh sửa</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit"></i> Thông tin tài nguyên</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.club-resources.update', $resource->id) }}" enctype="multipart/form-data" id="resourceForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Tiêu đề -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            <i class="fas fa-heading text-primary"></i> Tiêu đề tài nguyên 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $resource->title) }}" 
                               placeholder="Ví dụ: Mẫu đơn xin gia nhập CLB"
                               minlength="5"
                               maxlength="255"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Tiêu đề ngắn gọn, mô tả rõ ràng tài nguyên</div>
                    </div>

                    <!-- Mô tả -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">
                            <i class="fas fa-align-left text-primary"></i> Mô tả
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Mô tả chi tiết về tài nguyên này..."
                                  maxlength="1000">{{ old('description', $resource->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Mô tả giúp người dùng hiểu rõ hơn về tài nguyên</div>
                    </div>

                    <!-- File hiện tại -->
                    @if($resource->file_path)
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-file text-primary"></i> File hiện tại
                        </label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        @if($resource->thumbnail_url)
                                            <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" class="img-fluid rounded">
                                        @else
                                            <i class="fas fa-file fa-3x text-muted"></i>
                                        @endif
                                    </div>
                                    <div class="col-md-7">
                                        <h6 class="mb-1">{{ $resource->file_name }}</h6>
                                        <p class="text-muted mb-0">
                                            <small>{{ $resource->formatted_file_size }} | {{ $resource->file_type }}</small>
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <a href="{{ route('admin.club-resources.download', $resource->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download"></i> Tải xuống
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Upload file mới hoặc link -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-upload text-primary"></i> Thay đổi file hoặc link
                        </label>
                        
                        <ul class="nav nav-tabs mb-3" id="uploadTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-upload" type="button" role="tab">
                                    <i class="fas fa-file-upload"></i> Tải file mới
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="link-tab" data-bs-toggle="tab" data-bs-target="#link-input" type="button" role="tab">
                                    <i class="fas fa-link"></i> Cập nhật link
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="uploadTabContent">
                            <!-- File Upload Tab -->
                            <div class="tab-pane fade show active" id="file-upload" role="tabpanel">
                                <div class="upload-area border-2 border-dashed rounded p-4 text-center">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Kéo thả file vào đây hoặc click để chọn</h6>
                                    <p class="text-muted small mb-3">Hỗ trợ: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, MP4 (Tối đa 20MB)</p>
                                    <input type="file" 
                                           id="file" 
                                           name="file" 
                                           class="d-none"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi">
                                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('file').click()">
                                        <i class="fas fa-plus"></i> Chọn file mới
                                    </button>
                                </div>
                                <div id="filePreview" class="mt-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file"></i> <span id="fileName"></span>
                                        <button type="button" class="btn-close float-end" onclick="clearFile()"></button>
                                    </div>
                                </div>
                                <small class="text-muted">* Để trống nếu không muốn thay đổi file</small>
                            </div>
                            
                            <!-- Link Input Tab -->
                            <div class="tab-pane fade" id="link-input" role="tabpanel">
                                <input type="url" 
                                       class="form-control @error('external_link') is-invalid @enderror" 
                                       id="external_link" 
                                       name="external_link" 
                                       value="{{ old('external_link', $resource->external_link) }}" 
                                       placeholder="https://example.com/document.pdf">
                                @error('external_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nhập link Google Drive, Dropbox, YouTube, v.v.</div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
                
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-cog"></i> Cài đặt</h6>
            </div>
            <div class="card-body">
                <!-- Câu lạc bộ -->
                <div class="mb-3">
                    <label for="club_id" class="form-label fw-bold">
                        <i class="fas fa-users text-secondary"></i> Câu lạc bộ 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('club_id') is-invalid @enderror" id="club_id" name="club_id" required>
                        <option value="">Chọn câu lạc bộ</option>
                        @foreach($clubs as $club)
                            <option value="{{ $club->id }}" {{ old('club_id', $resource->club_id) == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('club_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Loại tài nguyên -->
                <div class="mb-3">
                    <label for="resource_type" class="form-label fw-bold">
                        <i class="fas fa-tag text-secondary"></i> Loại tài nguyên 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('resource_type') is-invalid @enderror" id="resource_type" name="resource_type" required>
                        <option value="form" {{ old('resource_type', $resource->resource_type) == 'form' ? 'selected' : '' }}>📋 Mẫu đơn</option>
                        <option value="image" {{ old('resource_type', $resource->resource_type) == 'image' ? 'selected' : '' }}>🖼️ Hình ảnh</option>
                        <option value="video" {{ old('resource_type', $resource->resource_type) == 'video' ? 'selected' : '' }}>🎥 Video</option>
                        <option value="pdf" {{ old('resource_type', $resource->resource_type) == 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                        <option value="document" {{ old('resource_type', $resource->resource_type) == 'document' ? 'selected' : '' }}>📝 Tài liệu</option>
                        <option value="guide" {{ old('resource_type', $resource->resource_type) == 'guide' ? 'selected' : '' }}>📖 Hướng dẫn</option>
                        <option value="other" {{ old('resource_type', $resource->resource_type) == 'other' ? 'selected' : '' }}>📦 Khác</option>
                    </select>
                    @error('resource_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Trạng thái -->
                <div class="mb-3">
                    <label for="status" class="form-label fw-bold">
                        <i class="fas fa-toggle-on text-secondary"></i> Trạng thái 
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', $resource->status) == 'active' ? 'selected' : '' }}>✅ Hoạt động</option>
                        <option value="inactive" {{ old('status', $resource->status) == 'inactive' ? 'selected' : '' }}>⏸️ Tạm dừng</option>
                        <option value="archived" {{ old('status', $resource->status) == 'archived' ? 'selected' : '' }}>📦 Lưu trữ</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><i class="fas fa-eye text-primary"></i> <strong>{{ $resource->view_count }}</strong> lượt xem</p>
                <p class="mb-0"><i class="fas fa-download text-success"></i> <strong>{{ $resource->download_count }}</strong> lượt tải</p>
            </div>
        </div>

        <!-- Nút lưu -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Cập nhật Tài nguyên
            </button>
            <a href="{{ route('admin.club-resources.show', $resource->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </div>
</div>
</form>
@endsection

@section('scripts')
<script>
// File upload handling
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        document.getElementById('fileName').textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

function clearFile() {
    document.getElementById('file').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// Drag and drop
const uploadArea = document.querySelector('.upload-area');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-primary', 'bg-light');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-primary', 'bg-light');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file').files = files;
        document.getElementById('fileName').textContent = files[0].name + ' (' + formatFileSize(files[0].size) + ')';
        document.getElementById('filePreview').style.display = 'block';
    }
});

// Form validation
document.getElementById('resourceForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const clubId = document.getElementById('club_id').value;
    const resourceType = document.getElementById('resource_type').value;
    const file = document.getElementById('file').files[0];
    
    if (title.length < 5) {
        alert('Tiêu đề phải có ít nhất 5 ký tự');
        e.preventDefault();
        return false;
    }
    
    if (!clubId) {
        alert('Vui lòng chọn câu lạc bộ');
        e.preventDefault();
        return false;
    }
    
    if (!resourceType) {
        alert('Vui lòng chọn loại tài nguyên');
        e.preventDefault();
        return false;
    }
    
    if (file && file.size > 20 * 1024 * 1024) {
        alert('File không được vượt quá 20MB');
        e.preventDefault();
        return false;
    }
    
    return true;
});
</script>

<style>
.upload-area {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}

.upload-area.border-primary {
    border-color: #0d6efd !important;
    background-color: #e7f3ff;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    font-weight: 600;
}
</style>
@endsection


