@extends('layouts.student')

@section('title', 'Tạo tài nguyên CLB')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-plus-circle text-teal me-2"></i> Tạo tài nguyên CLB
                    </h4>
                    <p class="text-muted mb-0">{{ $club->name ?? 'CLB' }}</p>
                </div>
                <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('student.club-management.resources.store', ['club' => $clubId]) }}" method="POST" enctype="multipart/form-data" id="resource-form">
                @csrf

                <!-- Basic Information -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Nhập tiêu đề tài nguyên" 
                                   required
                                   minlength="5"
                                   maxlength="255"
                                   oninput="validateTitle(this)">
                            <small class="form-text text-muted">
                                <span id="title-count">0</span>/255 ký tự (tối thiểu 5 ký tự)
                            </small>
                            @error('title')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div id="title-error" class="text-danger small mt-1" style="display: none;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4"
                                      placeholder="Nhập mô tả tài nguyên" 
                                      maxlength="1000"
                                      oninput="validateDescription(this)">{{ old('description') }}</textarea>
                            <small class="form-text text-muted">
                                <span id="description-count">0</span>/1000 ký tự
                            </small>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="external_link" class="form-label">Link ngoài (nếu có)</label>
                            <input type="url" class="form-control @error('external_link') is-invalid @enderror" 
                                   id="external_link" name="external_link" value="{{ old('external_link') }}" 
                                   placeholder="https://example.com"
                                   maxlength="500"
                                   onblur="validateExternalLink(this)">
                            @error('external_link')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Link đến tài nguyên bên ngoài (tùy chọn)</small>
                            <div id="external_link-error" class="text-danger small mt-1" style="display: none;"></div>
                        </div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="fas fa-file me-2"></i>Tải lên file</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="files" class="form-label">Album file tài nguyên</label>
                            <input type="file" class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror" 
                                   id="files" name="files[]" accept=".doc,.docx,.xls,.xlsx,.pdf" multiple onchange="previewFiles(this); validateFiles(this)">
                            <small class="form-text text-muted">
                                Hỗ trợ: DOC, DOCX, XLS, XLSX, PDF (Tối đa 20MB mỗi file, tối đa 10 file)
                            </small>
                            @error('files')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('files.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($errors->has('files.0') || $errors->has('files.1') || $errors->has('files.2') || $errors->has('files.3') || $errors->has('files.4') || $errors->has('files.5') || $errors->has('files.6') || $errors->has('files.7') || $errors->has('files.8') || $errors->has('files.9'))
                                <div class="text-danger small mt-1">
                                    @foreach(['files.0', 'files.1', 'files.2', 'files.3', 'files.4', 'files.5', 'files.6', 'files.7', 'files.8', 'files.9'] as $key)
                                        @error($key)
                                            <div>{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                </div>
                            @endif
                            <div id="files-error" class="text-danger small mt-1" style="display: none;"></div>
                        </div>

                        <div class="form-group" id="files-preview-section" style="display: none;">
                            <label>Album file đã chọn:</label>
                            <div class="row" id="files-preview-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Image & Video Upload -->
                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="fas fa-images me-2"></i>Album hình ảnh & video</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="images" class="form-label">Hình ảnh và video</label>
                            <input type="file" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" 
                                   id="images" name="images[]" accept="image/*,video/*" multiple onchange="previewImages(this); validateImages(this)">
                            <small class="form-text text-muted">
                                Hỗ trợ: JPG, PNG, GIF, MP4, AVI, MOV (Tối đa 100MB mỗi file, tối đa 10 file)
                            </small>
                            @error('images')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('images.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @if($errors->has('images.0') || $errors->has('images.1') || $errors->has('images.2') || $errors->has('images.3') || $errors->has('images.4') || $errors->has('images.5') || $errors->has('images.6') || $errors->has('images.7') || $errors->has('images.8') || $errors->has('images.9'))
                                <div class="text-danger small mt-1">
                                    @foreach(['images.0', 'images.1', 'images.2', 'images.3', 'images.4', 'images.5', 'images.6', 'images.7', 'images.8', 'images.9'] as $key)
                                        @error($key)
                                            <div>{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                </div>
                            @endif
                            <div id="images-error" class="text-danger small mt-1" style="display: none;"></div>
                        </div>

                        <div class="form-group" id="images-preview-section" style="display: none;">
                            <label>Album hình ảnh:</label>
                            <div class="row" id="images-preview-container"></div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Lưu tài nguyên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialize counts on page load
document.addEventListener('DOMContentLoaded', function() {
    const descriptionInput = document.getElementById('description');
    if (descriptionInput) {
        validateDescription(descriptionInput);
    }
    
    const titleInput = document.getElementById('title');
    if (titleInput) {
        validateTitle(titleInput);
    }
});

// Validate title
function validateTitle(input) {
    const count = input.value.length;
    const countSpan = document.getElementById('title-count');
    const errorDiv = document.getElementById('title-error');
    
    countSpan.textContent = count;
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';
    
    if (count < 5 && count > 0) {
        errorDiv.innerHTML = 'Tiêu đề phải có ít nhất 5 ký tự.';
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        countSpan.classList.add('text-danger');
    } else if (count > 255) {
        errorDiv.innerHTML = 'Tiêu đề không được vượt quá 255 ký tự.';
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        countSpan.classList.add('text-danger');
    } else {
        input.classList.remove('is-invalid');
        countSpan.classList.remove('text-danger');
    }
}

// File album preview functions
let selectedFiles = [];

// Validate files before upload
function validateFiles(input) {
    const errorDiv = document.getElementById('files-error');
    const fileInput = input;
    const files = Array.from(fileInput.files);
    
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';
    
    // Check number of files
    if (files.length > 10) {
        errorDiv.innerHTML = 'Bạn chỉ có thể chọn tối đa 10 file.';
        errorDiv.style.display = 'block';
        fileInput.setCustomValidity('Tối đa 10 file');
        return false;
    }
    
    // Check each file
    const allowedTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                         'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                         'application/pdf'];
    const maxSize = 20 * 1024 * 1024; // 20MB
    
    let errors = [];
    files.forEach((file, index) => {
        // Check file type
        if (!allowedTypes.includes(file.type)) {
            errors.push(`File "${file.name}" không đúng định dạng. Chỉ chấp nhận: DOC, DOCX, XLS, XLSX, PDF.`);
        }
        
        // Check file size
        if (file.size > maxSize) {
            errors.push(`File "${file.name}" vượt quá 20MB (${formatFileSize(file.size)}).`);
        }
    });
    
    if (errors.length > 0) {
        errorDiv.innerHTML = errors.join('<br>');
        errorDiv.style.display = 'block';
        fileInput.setCustomValidity(errors[0]);
        return false;
    }
    
    fileInput.setCustomValidity('');
    return true;
}

// Validate images before upload
function validateImages(input) {
    const errorDiv = document.getElementById('images-error');
    const imageInput = input;
    const files = Array.from(imageInput.files);
    
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';
    
    // Check number of files
    if (files.length > 10) {
        errorDiv.innerHTML = 'Bạn chỉ có thể chọn tối đa 10 hình ảnh/video.';
        errorDiv.style.display = 'block';
        imageInput.setCustomValidity('Tối đa 10 file');
        return false;
    }
    
    // Check each file
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 
                         'video/mp4', 'video/x-msvideo', 'video/quicktime'];
    const maxSize = 100 * 1024 * 1024; // 100MB
    
    let errors = [];
    files.forEach((file, index) => {
        // Check file type
        if (!allowedTypes.includes(file.type)) {
            errors.push(`File "${file.name}" không đúng định dạng. Chỉ chấp nhận: JPG, PNG, GIF, MP4, AVI, MOV.`);
        }
        
        // Check file size
        if (file.size > maxSize) {
            errors.push(`File "${file.name}" vượt quá 100MB (${formatFileSize(file.size)}).`);
        }
    });
    
    if (errors.length > 0) {
        errorDiv.innerHTML = errors.join('<br>');
        errorDiv.style.display = 'block';
        imageInput.setCustomValidity(errors[0]);
        return false;
    }
    
    imageInput.setCustomValidity('');
    return true;
}

// Validate description
function validateDescription(textarea) {
    const count = textarea.value.length;
    const countSpan = document.getElementById('description-count');
    countSpan.textContent = count;
    
    if (count > 1000) {
        textarea.classList.add('is-invalid');
        countSpan.classList.add('text-danger');
    } else {
        textarea.classList.remove('is-invalid');
        countSpan.classList.remove('text-danger');
    }
}

// Validate external link
function validateExternalLink(input) {
    const errorDiv = document.getElementById('external_link-error');
    const value = input.value.trim();
    
    errorDiv.style.display = 'none';
    errorDiv.innerHTML = '';
    
    if (value === '') {
        input.classList.remove('is-invalid');
        return true;
    }
    
    // Basic URL validation
    try {
        const url = new URL(value);
        if (!['http:', 'https:'].includes(url.protocol)) {
            errorDiv.innerHTML = 'Link phải bắt đầu bằng http:// hoặc https://';
            errorDiv.style.display = 'block';
            input.classList.add('is-invalid');
            return false;
        }
    } catch (e) {
        errorDiv.innerHTML = 'Link không hợp lệ. Vui lòng nhập URL đúng định dạng (ví dụ: https://example.com)';
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        return false;
    }
    
    if (value.length > 500) {
        errorDiv.innerHTML = 'Link không được vượt quá 500 ký tự.';
        errorDiv.style.display = 'block';
        input.classList.add('is-invalid');
        return false;
    }
    
    input.classList.remove('is-invalid');
    return true;
}

// Validate form before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const externalLinkInput = document.getElementById('external_link');
    const filesInput = document.getElementById('files');
    const imagesInput = document.getElementById('images');
    
    let isValid = true;
    
    // Validate title
    if (!titleInput.value.trim()) {
        e.preventDefault();
        titleInput.focus();
        titleInput.classList.add('is-invalid');
        isValid = false;
    } else if (titleInput.value.trim().length < 5) {
        e.preventDefault();
        titleInput.focus();
        titleInput.classList.add('is-invalid');
        alert('Tiêu đề phải có ít nhất 5 ký tự.');
        isValid = false;
    } else if (titleInput.value.trim().length > 255) {
        e.preventDefault();
        titleInput.focus();
        titleInput.classList.add('is-invalid');
        alert('Tiêu đề không được vượt quá 255 ký tự.');
        isValid = false;
    }
    
    // Validate description
    if (descriptionInput.value.length > 1000) {
        e.preventDefault();
        descriptionInput.focus();
        descriptionInput.classList.add('is-invalid');
        alert('Mô tả không được vượt quá 1000 ký tự.');
        isValid = false;
    }
    
    // Validate external link
    if (externalLinkInput.value.trim() && !validateExternalLink(externalLinkInput)) {
        e.preventDefault();
        externalLinkInput.focus();
        isValid = false;
    }
    
    // Validate files
    if (filesInput.files.length > 0 && !validateFiles(filesInput)) {
        e.preventDefault();
        filesInput.focus();
        isValid = false;
    }
    
    // Validate images
    if (imagesInput.files.length > 0 && !validateImages(imagesInput)) {
        e.preventDefault();
        imagesInput.focus();
        isValid = false;
    }
    
    return isValid;
});

function previewFiles(input) {
    if (input.files && input.files.length > 0) {
        selectedFiles = Array.from(input.files);
        displayFileAlbum();
    }
}

function displayFileAlbum() {
    const container = document.getElementById('files-preview-container');
    const previewSection = document.getElementById('files-preview-section');
    
    container.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6 mb-3';
        
        const fileIcon = getFileIcon(file.type);
        const fileSize = formatFileSize(file.size);
        
        col.innerHTML = `
            <div class="card file-card border" data-index="${index}">
                <div class="card-body p-2">
                    <div class="text-center">
                        <i class="${fileIcon}" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    </div>
                    <div class="text-center">
                        <small class="text-muted d-block">${file.name}</small>
                        <small class="text-muted">${fileSize}</small>
                    </div>
                    <div class="mt-2 text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFileFromAlbum(${index})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
    
    previewSection.style.display = 'block';
}

function removeFileFromAlbum(index) {
    selectedFiles.splice(index, 1);
    
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('files').files = dt.files;
    
    displayFileAlbum();
}

function getFileIcon(fileType) {
    if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word text-primary';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    if (fileType.includes('pdf')) return 'fas fa-file-pdf text-danger';
    return 'fas fa-file text-muted';
}

// Image album preview functions
let selectedImages = [];

function previewImages(input) {
    if (input.files && input.files.length > 0) {
        selectedImages = Array.from(input.files);
        displayImageAlbum();
    }
}

function displayImageAlbum() {
    const container = document.getElementById('images-preview-container');
    const previewSection = document.getElementById('images-preview-section');
    
    container.innerHTML = '';
    
    selectedImages.forEach((file, index) => {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6 mb-3';
        
        if (file.type.includes('video')) {
            const videoUrl = URL.createObjectURL(file);
            col.innerHTML = `
                <div class="card image-card border" data-index="${index}">
                    <div class="card-body p-2">
                        <video controls class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <source src="${videoUrl}" type="${file.type}">
                        </video>
                        <div class="mt-2">
                            <small class="text-muted d-block">${file.name}</small>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeImageFromAlbum(${index})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            const reader = new FileReader();
            reader.onload = function(e) {
                col.innerHTML = `
                    <div class="card image-card border" data-index="${index}">
                        <div class="card-body p-2">
                            <img src="${e.target.result}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <div class="mt-2">
                                <small class="text-muted d-block">${file.name}</small>
                                <small class="text-muted">${formatFileSize(file.size)}</small>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImageFromAlbum(${index})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
        
        container.appendChild(col);
    });
    
    previewSection.style.display = 'block';
}

function removeImageFromAlbum(index) {
    selectedImages.splice(index, 1);
    
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById('images').files = dt.files;
    
    displayImageAlbum();
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>
@endpush
@endsection

