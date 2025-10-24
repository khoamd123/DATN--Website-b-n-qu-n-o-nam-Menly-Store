@extends('admin.layouts.app')

@section('title', 'Thêm tài nguyên CLB')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thêm tài nguyên CLB</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
    </div>
</div>
                <div class="card-body">
                    <form action="{{ route('admin.club-resources.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

<div class="row">
                            <div class="col-12">
                                <!-- Basic Information -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Thông tin cơ bản</h5>
            </div>
            <div class="card-body">
                                        <div class="form-group">
                                            <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                                   id="title" name="title" value="{{ old('title') }}" 
                                                   placeholder="Nhập tiêu đề tài nguyên" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                                        <div class="form-group">
                                            <label for="description">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" 
                                                      placeholder="Nhập mô tả tài nguyên">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                                        <div class="form-group">
                                            <label for="club_id">Câu lạc bộ <span class="text-danger">*</span></label>
                                            <select class="form-control @error('club_id') is-invalid @enderror" 
                                                    id="club_id" name="club_id" required>
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

                                        <div class="form-group">
                                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-control @error('status') is-invalid @enderror" 
                                                    id="status" name="status" required>
                                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

                                <!-- File Upload -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Tải lên file</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="files">Album file tài nguyên</label>
                                            <input type="file" class="form-control @error('files') is-invalid @enderror" 
                                                   id="files" name="files[]" accept=".doc,.docx,.xls,.xlsx" multiple onchange="previewFiles(this)">
                                            <small class="form-text text-muted">
                                                Hỗ trợ: DOC, DOCX, XLS, XLSX (Tối đa 20MB mỗi file, tối đa 10 file)
                                            </small>
                                            @error('files')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- File Album Preview -->
                                        <div class="form-group" id="files-preview-section" style="display: none;">
                                            <label>Album file đã chọn:</label>
                                            <div class="row" id="files-preview-container">
                                                <!-- Files will be added here dynamically -->
                                            </div>
                                        </div>

                                        <!-- Image & Video Album Section -->
                                        <div class="form-group">
                                            <label for="images">Album hình ảnh & video</label>
                                            <input type="file" class="form-control @error('images') is-invalid @enderror" 
                                                   id="images" name="images[]" accept="image/*,video/*" multiple onchange="previewImages(this)">
                                            <small class="form-text text-muted">
                                                Hỗ trợ: JPG, PNG, GIF, MP4, AVI, MOV (Tối đa 100MB mỗi file, tối đa 10 file)
                                            </small>
                                            @error('images')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <!-- Image Album Preview -->
                                        <div class="form-group" id="images-preview-section" style="display: none;">
                                            <label>Album hình ảnh:</label>
                                            <div class="row" id="images-preview-container">
                                                <!-- Images will be added here dynamically -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- External Link -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title">Link ngoài</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="external_link">URL</label>
                                            <input type="url" class="form-control @error('external_link') is-invalid @enderror" 
                                                   id="external_link" name="external_link" value="{{ old('external_link') }}" 
                                                   placeholder="https://example.com">
                                            @error('external_link')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-group text-center">
            <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Lưu tài nguyên
            </button>
                                    <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary btn-lg ml-2">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </div>
</div>
</form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CKEditor Script -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input change handler (if needed for other purposes)
    const fileInput = document.getElementById('file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            console.log('File selected:', e.target.files[0]?.name);
        });
    }
});

// File album preview functions
let selectedFiles = [];

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
            <div class="card file-card" data-index="${index}">
                <div class="card-body p-2">
                    <div class="text-center">
                        <i class="${fileIcon}" style="font-size: 3rem; margin-bottom: 10px;"></i>
                    </div>
                    <div class="text-center">
                        <small class="text-muted">${file.name}</small>
                        <br>
                        <small class="text-muted">${fileSize}</small>
                    </div>
                    <div class="mt-2 text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeFileFromAlbum(${index})">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="setFilePrimary(${index})">
                            <i class="fas fa-star"></i> File chính
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
    
    // Update file input
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    document.getElementById('files').files = dt.files;
    
    displayFileAlbum();
}

function setFilePrimary(index) {
    // Remove primary class from all files
    document.querySelectorAll('.file-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    
    // Add primary class to selected file
    const selectedCard = document.querySelector(`[data-index="${index}"]`);
    if (selectedCard) {
        selectedCard.classList.add('border-primary');
    }
}

function getFileIcon(fileType) {
    if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word text-primary';
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    
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
            // Video preview
            const videoUrl = URL.createObjectURL(file);
            col.innerHTML = `
                <div class="card image-card" data-index="${index}">
                    <div class="card-body p-2">
                        <video controls class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <source src="${videoUrl}" type="${file.type}">
                            Your browser does not support the video tag.
                        </video>
                        <div class="mt-2">
                            <small class="text-muted">${file.name}</small>
                            <br>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeImageFromAlbum(${index})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setPrimaryImage(${index})">
                                <i class="fas fa-star"></i> Video chính
                            </button>
                        </div>
                    </div>
                </div>
            `;
        } else {
            // Image preview
            const reader = new FileReader();
            reader.onload = function(e) {
                col.innerHTML = `
                    <div class="card image-card" data-index="${index}">
                        <div class="card-body p-2">
                            <img src="${e.target.result}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <div class="mt-2">
                                <small class="text-muted">${file.name}</small>
                                <br>
                                <small class="text-muted">${formatFileSize(file.size)}</small>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeImageFromAlbum(${index})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" onclick="setPrimaryImage(${index})">
                                    <i class="fas fa-star"></i> Ảnh chính
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
    
    // Update file input
    const dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById('images').files = dt.files;
    
    displayImageAlbum();
}

function setPrimaryImage(index) {
    // Remove primary class from all images
    document.querySelectorAll('.image-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    
    // Add primary class to selected image
    const selectedCard = document.querySelector(`[data-index="${index}"]`);
    if (selectedCard) {
        selectedCard.classList.add('border-primary');
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// CKEditor for description
ClassicEditor
    .create(document.querySelector('#description'), {
        toolbar: [
            'heading', '|',
            'bold', 'italic', '|',
            'bulletedList', 'numberedList', '|',
            'blockQuote', '|',
            'link', '|',
            'undo', 'redo'
        ],
        language: 'vi'
    })
    .then(editor => {
        console.log('CKEditor for description initialized successfully');
    })
    .catch(error => {
        console.error('Error initializing CKEditor for description:', error);
});
</script>

<style>
.image-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.image-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.image-card.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.image-card .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

#images-preview-container .col-md-4,
#files-preview-container .col-md-4 {
    margin-bottom: 1rem;
}

.primary-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
}

/* File Album Styles */
.file-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.file-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.file-card.border-primary {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.file-card .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endsection
