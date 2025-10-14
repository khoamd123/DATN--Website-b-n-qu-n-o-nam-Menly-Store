@extends('admin.layouts.app')

@section('title', 'Thêm Bài viết - CLB Admin')

@section('content')
<div class="content-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-1">Tạo Bài viết Mới</h1>
        <p class="text-muted mb-0">Viết và chia sẻ nội dung với cộng đồng</p>
    </div>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Bài viết</a></li>
            <li class="breadcrumb-item active">Tạo mới</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="fas fa-edit"></i> Nội dung bài viết</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" id="postForm" novalidate>
                    @csrf
                    
                    <!-- Tiêu đề -->
                    <div class="mb-4">
                        <label for="title" class="form-label fw-bold">
                            Tiêu đề bài viết 
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
                               placeholder="Nhập tiêu đề hấp dẫn..."
                               minlength="10"
                               maxlength="255"
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                 
                    </div>

                    <!-- Nội dung -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-bold">
                             Nội dung bài viết 
                            <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" 
                                  name="content" 
                                  rows="12" 
                                  placeholder="Viết nội dung bài viết của bạn ở đây..."
                                  minlength="50"
                                  maxlength="50000"
                                  required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                   
                    </div>

                    <!-- Upload ảnh -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-images text-primary"></i> Hình ảnh bài viết
                        </label>
                        <div class="upload-area border-2 border-dashed rounded p-4 text-center" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Kéo thả ảnh vào đây hoặc click để chọn</h6>
                            <p class="text-muted small mb-3">Hỗ trợ: JPG, PNG, GIF (Tối đa 5MB mỗi ảnh)</p>
                            <input type="file" 
                                   id="images" 
                                   name="images[]" 
                                   multiple 
                                   accept="image/*" 
                                   class="d-none"
                                   onchange="handleFileSelect(event)">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('images').click()">
                                <i class="fas fa-plus"></i> Chọn ảnh
                            </button>
                        </div>
                        
                        <!-- Preview ảnh -->
                        <div id="imagePreview" class="row mt-3" style="display: none;">
                            <div class="col-12">
                                <h6 class="mb-2">Ảnh đã chọn:</h6>
                                <div id="previewContainer" class="row"></div>
                            </div>
                        </div>
                        
                        @error('images')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                        @error('images.*')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
            </div>
        </div>
    </div>
                
                <div class="col-md-4">
                    <!-- Câu lạc bộ -->
                    <div class="mb-3">
                        <label for="club_id" class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                        <select class="form-select @error('club_id') is-invalid @enderror" 
                                id="club_id" 
                                name="club_id" 
                                required>
                            <option value="">Chọn câu lạc bộ</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('club_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Loại bài viết -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Chọn loại</option>
                            <option value="post" {{ old('type') == 'post' ? 'selected' : '' }}>Bài viết</option>
                            <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Trạng thái -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Chọn trạng thái</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                            <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="previewPost()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu bài viết
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewPost() {
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;
    const type = document.getElementById('type').value;
    
    if (title && content) {
        const preview = document.getElementById('preview');
        const typeLabel = type === 'announcement' ? 'Thông báo' : 'Bài viết';
        
        preview.innerHTML = `
            <div class="mb-2">
                <span class="badge bg-${type === 'announcement' ? 'danger' : 'primary'}">${typeLabel}</span>
            </div>
            <h5>${title}</h5>
            <div class="text-muted small mb-2">${new Date().toLocaleDateString('vi-VN')}</div>
            <div>${content.substring(0, 200)}${content.length > 200 ? '...' : ''}</div>
        `;
    }
}

// Auto preview when typing
document.getElementById('title').addEventListener('input', previewPost);
document.getElementById('content').addEventListener('input', previewPost);
document.getElementById('type').addEventListener('change', previewPost);

// Form validation
document.getElementById('postForm').addEventListener('submit', function(e) {
    let isValid = true;
    const errors = [];
    
    // Validate title
    const title = document.getElementById('title');
    if (title.value.trim().length < 10) {
        isValid = false;
        errors.push('Tiêu đề phải có ít nhất 10 ký tự');
        title.classList.add('is-invalid');
    } else if (title.value.trim().length > 255) {
        isValid = false;
        errors.push('Tiêu đề không được vượt quá 255 ký tự');
        title.classList.add('is-invalid');
    } else {
        title.classList.remove('is-invalid');
    }
    
    // Validate content
    const content = document.getElementById('content');
    if (content.value.trim().length < 50) {
        isValid = false;
        errors.push('Nội dung phải có ít nhất 50 ký tự');
        content.classList.add('is-invalid');
    } else if (content.value.trim().length > 50000) {
        isValid = false;
        errors.push('Nội dung không được vượt quá 50,000 ký tự');
        content.classList.add('is-invalid');
    } else {
        content.classList.remove('is-invalid');
    }
    
    // Validate club
    const clubId = document.getElementById('club_id');
    if (!clubId.value) {
        isValid = false;
        errors.push('Vui lòng chọn câu lạc bộ');
        clubId.classList.add('is-invalid');
    } else {
        clubId.classList.remove('is-invalid');
    }
    
    // Validate type
    const type = document.getElementById('type');
    if (!type.value) {
        isValid = false;
        errors.push('Vui lòng chọn loại bài viết');
        type.classList.add('is-invalid');
    } else {
        type.classList.remove('is-invalid');
    }
    
    // Validate status
    const status = document.getElementById('status');
    if (!status.value) {
        isValid = false;
        errors.push('Vui lòng chọn trạng thái');
        status.classList.add('is-invalid');
    } else {
        status.classList.remove('is-invalid');
    }
    
    // Validate images
    const images = document.getElementById('images');
    if (images.files.length > 10) {
        isValid = false;
        errors.push('Chỉ được upload tối đa 10 ảnh');
    }
    
    for (let i = 0; i < images.files.length; i++) {
        const file = images.files[i];
        // Check file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            isValid = false;
            errors.push(`Ảnh "${file.name}" vượt quá 5MB`);
        }
        // Check file type
        if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'].includes(file.type)) {
            isValid = false;
            errors.push(`File "${file.name}" không phải là ảnh hợp lệ`);
        }
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Vui lòng kiểm tra lại:\n\n' + errors.join('\n'));
        return false;
    }
    
    return true;
});

// Real-time character count
document.getElementById('title').addEventListener('input', function() {
    const length = this.value.length;
    const formText = this.parentElement.querySelector('.form-text');
    formText.innerHTML = `Tiêu đề: ${length}/255 ký tự`;
    
    if (length < 10) {
        formText.classList.add('text-danger');
        formText.classList.remove('text-success');
    } else {
        formText.classList.remove('text-danger');
        formText.classList.add('text-success');
    }
});

document.getElementById('content').addEventListener('input', function() {
    const length = this.value.length;
    const formText = this.parentElement.querySelector('.form-text');
    formText.innerHTML = `Nội dung: ${length}/50,000 ký tự`;
    
    if (length < 50) {
        formText.classList.add('text-danger');
        formText.classList.remove('text-success');
    } else {
        formText.classList.remove('text-danger');
        formText.classList.add('text-success');
    }
});

// Image upload handling
let selectedImages = [];

function handleFileSelect(event) {
    const files = event.target.files;
    selectedImages = Array.from(files);
    displayImagePreviews();
}

function displayImagePreviews() {
    const previewContainer = document.getElementById('previewContainer');
    const imagePreview = document.getElementById('imagePreview');
    
    previewContainer.innerHTML = '';
    
    if (selectedImages.length > 0) {
        imagePreview.style.display = 'block';
        
        selectedImages.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'col-md-3 mb-3';
                    imageDiv.innerHTML = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${file.name}</small>
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="featured_image" value="${index}" id="featured_${index}">
                                        <label class="form-check-label small" for="featured_${index}">
                                            Ảnh đại diện
                                        </label>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="removeImage(${index})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    previewContainer.appendChild(imageDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        imagePreview.style.display = 'none';
    }
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    
    // Update file input
    const fileInput = document.getElementById('images');
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
    
    displayImagePreviews();
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');

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
    
    const files = Array.from(e.dataTransfer.files);
    const imageFiles = files.filter(file => file.type.startsWith('image/'));
    
    selectedImages = [...selectedImages, ...imageFiles];
    
    // Update file input
    const fileInput = document.getElementById('images');
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    fileInput.files = dt.files;
    
    displayImagePreviews();
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

.card-img-top {
    transition: transform 0.2s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}
</style>
@endsection
