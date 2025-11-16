@extends('layouts.student')

@section('title', 'Tạo tài nguyên - ' . $club->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="text-decoration-none mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                    <h4 class="mb-0"><i class="fas fa-folder-plus me-2"></i>Tạo tài nguyên - {{ $club->name }}</h4>
                </div>
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

            <form method="POST" action="{{ route('student.club-management.resources.store', ['club' => $clubId]) }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin cơ bản</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           name="title" value="{{ old('title') }}" 
                                           placeholder="Nhập tiêu đề tài nguyên" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mô tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="10" 
                                              placeholder="Nhập mô tả tài nguyên">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- File Upload -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-upload me-2"></i>Tải lên file</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Album file tài nguyên</label>
                                    <input type="file" class="form-control @error('files') is-invalid @enderror" 
                                           id="files-input" name="files[]" accept=".doc,.docx,.xls,.xlsx,.xlsm,.pdf,.ppt,.pptx" multiple>
                                    <small class="form-text text-muted">
                                        Hỗ trợ: DOC, DOCX, XLS, XLSX, XLSM, PDF, PPT, PPTX (Tối đa 20MB mỗi file, tối đa 10 file)
                                    </small>
                                    <div id="files-list" class="mt-2"></div>
                                    @error('files')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Album hình ảnh & video</label>
                                    <input type="file" class="form-control @error('images') is-invalid @enderror" 
                                           id="images-input" name="images[]" accept="image/*,video/*" multiple>
                                    <small class="form-text text-muted">
                                        Hỗ trợ: JPG, PNG, GIF, WEBP, MP4, AVI, MOV, WEBM (Tối đa 100MB mỗi file, tối đa 10 file)
                                    </small>
                                    <div id="images-list" class="mt-2"></div>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- External Link -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-link me-2"></i>Link ngoài</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-0">
                                    <label class="form-label">URL</label>
                                    <input type="url" class="form-control @error('external_link') is-invalid @enderror" 
                                           name="external_link" value="{{ old('external_link') }}" 
                                           placeholder="https://example.com">
                                    @error('external_link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Settings -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <strong><i class="fas fa-cog me-1"></i>Cài đặt</strong>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                        <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Câu lạc bộ: <strong>{{ $club->name }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Lưu tài nguyên
                            </button>
                            <a href="{{ route('student.club-management.resources', ['club' => $clubId]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Hủy
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('student.posts.upload-image'), 'csrfToken' => csrf_token()])
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionTextarea = document.querySelector('textarea#description');
        
        if (descriptionTextarea) {
            // Tạo SimpleUploadAdapterPlugin từ factory
            const SimpleUploadAdapterPlugin = window.CKEditorUploadAdapterFactory(
                '{{ route("student.posts.upload-image") }}',
                '{{ csrf_token() }}'
            );

            ClassicEditor.create(descriptionTextarea, {
                extraPlugins: [SimpleUploadAdapterPlugin],
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'link', '|',
                        'bulletedList', 'numberedList', '|',
                        'blockQuote', 'insertTable', '|',
                        'uploadImage', '|',
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
                },
                language: 'vi'
            }).then(function(editor) {
                console.log('CKEditor initialized for description');
            }).catch(function(error) {
                console.error('Error initializing CKEditor:', error);
            });
        }

        // File list display
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }


        // Store selected files
        let selectedFiles = [];
        let selectedImages = [];

        // Update input file from array
        function updateFileInput(input, filesArray) {
            const dt = new DataTransfer();
            filesArray.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        // Display file list with delete option
        function displayFileListWithDelete(input, listContainer, filesArray, isImages = false) {
            listContainer.innerHTML = '';
            
            if (filesArray.length > 0) {
                const row = document.createElement('div');
                row.className = 'row g-3';
                
                filesArray.forEach((file, index) => {
                    const col = document.createElement('div');
                    col.className = 'col-md-4 col-sm-6';
                    
                    const card = document.createElement('div');
                    card.className = 'card';
                    
                    // Kiểm tra xem file có phải là ảnh hoặc video không
                    const isImage = file.type.startsWith('image/');
                    const isVideo = file.type.startsWith('video/');
                    
                    let previewHtml = '';
                    if (isImage) {
                        const imageUrl = URL.createObjectURL(file);
                        previewHtml = `
                            <img src="${imageUrl}" class="card-img-top" alt="${file.name}" 
                                 style="height: 200px; object-fit: cover; cursor: pointer;" 
                                 onclick="window.open('${imageUrl}', '_blank')">
                        `;
                    } else if (isVideo) {
                        const videoUrl = URL.createObjectURL(file);
                        previewHtml = `
                            <video class="card-img-top" style="height: 200px; object-fit: cover;" muted>
                                <source src="${videoUrl}" type="${file.type}">
                            </video>
                            <div style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 5px;">
                                <i class="fas fa-play me-1"></i> Video
                            </div>
                        `;
                    } else {
                        previewHtml = `
                            <div class="card-img-top d-flex align-items-center justify-content-center" 
                                 style="height: 200px; background-color: #f8f9fa;">
                                <i class="fas fa-file fa-3x text-muted"></i>
                            </div>
                        `;
                    }
                    
                    card.innerHTML = `
                        <div style="position: relative;">
                            ${previewHtml}
                            <button type="button" class="btn btn-danger btn-sm" 
                                    style="position: absolute; top: 5px; right: 5px; z-index: 10;"
                                    onclick="removeFile(${index}, ${isImages})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1" style="font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${file.name}">
                                ${file.name}
                            </h6>
                            <small class="text-muted">${formatFileSize(file.size)}</small>
                        </div>
                    `;
                    
                    col.appendChild(card);
                    row.appendChild(col);
                });
                
                listContainer.appendChild(row);
            }
        }

        // Remove file function
        window.removeFile = function(index, isImages) {
            if (isImages) {
                selectedImages.splice(index, 1);
                updateFileInput(imagesInput, selectedImages);
                displayFileListWithDelete(imagesInput, imagesList, selectedImages, true);
            } else {
                selectedFiles.splice(index, 1);
                updateFileInput(filesInput, selectedFiles);
                displayFileListWithDelete(filesInput, filesList, selectedFiles, false);
            }
        };

        // Handle files input
        const filesInput = document.getElementById('files-input');
        const filesList = document.getElementById('files-list');
        if (filesInput && filesList) {
            filesInput.addEventListener('change', function() {
                // Add new files to array
                Array.from(this.files).forEach(file => {
                    if (!selectedFiles.find(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                        selectedFiles.push(file);
                    }
                });
                updateFileInput(filesInput, selectedFiles);
                displayFileListWithDelete(filesInput, filesList, selectedFiles, false);
            });
        }

        // Handle images input
        const imagesInput = document.getElementById('images-input');
        const imagesList = document.getElementById('images-list');
        if (imagesInput && imagesList) {
            imagesInput.addEventListener('change', function() {
                // Add new files to array
                Array.from(this.files).forEach(file => {
                    if (!selectedImages.find(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified)) {
                        selectedImages.push(file);
                    }
                });
                updateFileInput(imagesInput, selectedImages);
                displayFileListWithDelete(imagesInput, imagesList, selectedImages, true);
            });
        }
    });
</script>
@endpush

