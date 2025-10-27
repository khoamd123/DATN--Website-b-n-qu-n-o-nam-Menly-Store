@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa tài nguyên CLB')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chỉnh sửa tài nguyên CLB</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.club-resources.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <a href="{{ route('admin.club-resources.show', $resource->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.club-resources.update', $resource->id) }}" method="POST"
                            enctype="multipart/form-data"
                            onsubmit="debugFormData(event); return submitFormWithDeletions(event)">
                            @csrf
                            @method('PUT')

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
                                                    id="title" name="title" value="{{ old('title', $resource->title) }}"
                                                    placeholder="Nhập tiêu đề tài nguyên" required>
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="description">Mô tả</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror"
                                                    id="description" name="description"
                                                    placeholder="Nhập mô tả tài nguyên">{{ old('description', $resource->description) }}</textarea>
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
                                                        <option value="{{ $club->id }}" {{ old('club_id', $resource->club_id) == $club->id ? 'selected' : '' }}>
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
                                                    <option value="active" {{ old('status', $resource->status) == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                                    <option value="inactive" {{ old('status', $resource->status) == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                                    <option value="archived" {{ old('status', $resource->status) == 'archived' ? 'selected' : '' }}>Lưu trữ</option>
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
                                            <h5 class="card-title">Tải lên file mới</h5>
                                        </div>
                                        <div class="card-body">
                                            @if($resource->file_path)
                                                <div class="alert alert-info">
                                                    <strong>File hiện tại:</strong> {{ $resource->file_name }}
                                                    <br><small>Chọn file mới để thay thế file hiện tại</small>
                                                </div>
                                            @endif

                                            <!-- Current File Album Display -->
                                            @if($resource->files && $resource->files->count() > 0)
                                                <div class="form-group">
                                                    <label>Album file hiện tại ({{ $resource->files->count() }} file):</label>
                                                    <div class="row" id="current-files-container">
                                                        @foreach($resource->files as $index => $file)
                                                            <div class="col-md-4 col-sm-6 mb-3">
                                                                <div class="card current-file-card {{ $file->is_primary ? 'border-primary' : '' }}"
                                                                    data-file-id="{{ $file->id }}">
                                                                    <div class="card-body p-2">
                                                                        <div class="text-center">
                                                                            <i class="{{ $file->file_icon }}"
                                                                                style="font-size: 3rem; margin-bottom: 10px;"></i>
                                                                        </div>
                                                                        @if($file->is_primary)
                                                                            <div class="primary-badge">
                                                                                <i class="fas fa-star"></i>
                                                                            </div>
                                                                        @endif
                                                                        <div class="text-center">
                                                                            <small class="text-muted">{{ $file->file_name }}</small>
                                                                            <br>
                                                                            <small
                                                                                class="text-muted">{{ $file->formatted_size }}</small>
                                                                        </div>
                                                                        <div class="mt-2 text-center">
                                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                                onclick="console.log('File delete button clicked for ID: {{ $file->id }}'); removeCurrentFile({{ $file->id }})">
                                                                                <i class="fas fa-trash"></i> Xóa
                                                                            </button>
                                                                            @if(!$file->is_primary)
                                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                                    onclick="console.log('File primary button clicked for ID: {{ $file->id }}'); setCurrentFilePrimary({{ $file->id }})">
                                                                                    <i class="fas fa-star"></i> File chính
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Add New Files Section -->
                                            <div class="form-group">
                                                <label for="files">Thêm file mới</label>
                                                <input type="file" class="form-control @error('files') is-invalid @enderror"
                                                    id="files" name="files[]" accept=".doc,.docx,.xls,.xlsx" multiple
                                                    onchange="previewNewFiles(this)">
                                                <small class="form-text text-muted">
                                                    Hỗ trợ: DOC, DOCX, XLS, XLSX (Tối đa 20MB mỗi file, tối đa 10 file)
                                                </small>
                                                @error('files')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- New Files Preview -->
                                            <div class="form-group" id="new-files-preview-section" style="display: none;">
                                                <label>File mới sẽ thêm:</label>
                                                <div class="row" id="new-files-preview-container">
                                                    <!-- New files will be added here dynamically -->
                                                </div>
                                            </div>

                                            <!-- File Preview -->
                                            <div class="form-group" id="file-preview-section" style="display: none;">
                                                <label>File mới đã chọn:</label>
                                                <div class="file-preview-container">
                                                    <!-- Video Preview -->
                                                    <div id="video-preview" style="display: none;">
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <video id="preview-video" controls class="img-fluid rounded"
                                                                    style="max-height: 300px; width: 100%;">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                                <div
                                                                    class="mt-2 d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <i class="fas fa-video" id="video-icon"></i>
                                                                        <span id="video-name"></span>
                                                                        <small class="text-muted" id="video-size"></small>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-danger"
                                                                        onclick="removeFile()">
                                                                        <i class="fas fa-trash"></i> Xóa
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Other File Preview -->
                                                    <div id="other-file-preview" style="display: none;">
                                                        <div
                                                            class="alert alert-info d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-file" id="file-icon"></i>
                                                                <span id="file-name"></span>
                                                                <small class="text-muted" id="file-size"></small>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="removeFile()">
                                                                <i class="fas fa-trash"></i> Xóa
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Current Image Album Display -->
                                            @if($resource->images && $resource->images->count() > 0)
                                                <div class="form-group">
                                                    <label>Album hình ảnh hiện tại ({{ $resource->images->count() }}
                                                        ảnh):</label>
                                                    <div class="row" id="current-images-container">
                                                        @foreach($resource->images as $index => $image)
                                                            <div class="col-md-4 col-sm-6 mb-3">
                                                                <div class="card current-image-card {{ $image->is_primary ? 'border-primary' : '' }}"
                                                                    data-image-id="{{ $image->id }}">
                                                                    <div class="card-body p-2">
                                                                        <img src="{{ $image->thumbnail_url }}"
                                                                            alt="{{ $image->image_name }}" class="img-fluid rounded"
                                                                            style="height: 150px; width: 100%; object-fit: cover;">
                                                                        @if($image->is_primary)
                                                                            <div class="primary-badge">
                                                                                <i class="fas fa-star"></i>
                                                                            </div>
                                                                        @endif
                                                                        <div class="mt-2">
                                                                            <small
                                                                                class="text-muted">{{ $image->image_name }}</small>
                                                                            <br>
                                                                            <small
                                                                                class="text-muted">{{ $image->formatted_size }}</small>
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                                onclick="console.log('Image delete button clicked for ID: {{ $image->id }}'); removeCurrentImage({{ $image->id }})">
                                                                                <i class="fas fa-trash"></i> Xóa
                                                                            </button>
                                                                            @if(!$image->is_primary)
                                                                                <button type="button" class="btn btn-sm btn-primary"
                                                                                    onclick="console.log('Image primary button clicked for ID: {{ $image->id }}'); setCurrentImagePrimary({{ $image->id }})">
                                                                                    <i class="fas fa-star"></i> Ảnh chính
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Add New Images Section -->
                                            <div class="form-group">
                                                <label for="images">Thêm hình ảnh mới</label>
                                                <input type="file"
                                                    class="form-control @error('images') is-invalid @enderror" id="images"
                                                    name="images[]" accept="image/*,video/*" multiple
                                                    onchange="previewNewImages(this)">
                                                <small class="form-text text-muted">
                                                    Hỗ trợ: JPG, PNG, GIF, MP4, AVI, MOV (Tối đa 100MB mỗi file, tối đa 10
                                                    file)
                                                </small>
                                                @error('images')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- New Images Preview -->
                                            <div class="form-group" id="new-images-preview-section" style="display: none;">
                                                <label>Hình ảnh mới sẽ thêm:</label>
                                                <div class="row" id="new-images-preview-container">
                                                    <!-- New images will be added here dynamically -->
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
                                                <input type="url"
                                                    class="form-control @error('external_link') is-invalid @enderror"
                                                    id="external_link" name="external_link"
                                                    value="{{ old('external_link', $resource->external_link) }}"
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
                                            <i class="fas fa-save"></i> Cập nhật tài nguyên
                                        </button>
                                        <a href="{{ route('admin.club-resources.show', $resource->id) }}"
                                            class="btn btn-secondary btn-lg ml-2">
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
        document.addEventListener('DOMContentLoaded', function () {
            // File input change handler (if needed for other purposes)
            const fileInput = document.getElementById('file');
            if (fileInput) {
                fileInput.addEventListener('change', function (e) {
                    console.log('File selected:', e.target.files[0]?.name);
                });
            }
        });

        // File preview functions
        function previewFile(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                const fileType = file.type;

                // Check if it's a video file
                if (fileType.includes('video')) {
                    // Show video preview
                    const videoPreview = document.getElementById('video-preview');
                    const otherFilePreview = document.getElementById('other-file-preview');

                    videoPreview.style.display = 'block';
                    otherFilePreview.style.display = 'none';

                    // Set video source
                    const video = document.getElementById('preview-video');
                    const url = URL.createObjectURL(file);
                    video.src = url;

                    // Set video info
                    document.getElementById('video-name').textContent = fileName;
                    document.getElementById('video-size').textContent = `(${fileSize})`;

                } else {
                    // Show other file preview
                    const videoPreview = document.getElementById('video-preview');
                    const otherFilePreview = document.getElementById('other-file-preview');

                    videoPreview.style.display = 'none';
                    otherFilePreview.style.display = 'block';

                    // Set file icon based on type
                    let iconClass = 'fas fa-file';
                    if (fileType.includes('pdf')) iconClass = 'fas fa-file-pdf text-danger';
                    else if (fileType.includes('word') || fileType.includes('document')) iconClass = 'fas fa-file-word text-primary';
                    else if (fileType.includes('excel') || fileType.includes('spreadsheet')) iconClass = 'fas fa-file-excel text-success';
                    else if (fileType.includes('powerpoint') || fileType.includes('presentation')) iconClass = 'fas fa-file-powerpoint text-warning';
                    else if (fileType.includes('image')) iconClass = 'fas fa-file-image text-info';

                    document.getElementById('file-icon').className = iconClass;
                    document.getElementById('file-name').textContent = fileName;
                    document.getElementById('file-size').textContent = `(${fileSize})`;
                }

                document.getElementById('file-preview-section').style.display = 'block';
            }
        }

        function removeFile() {
            // Clear file input
            document.getElementById('file').value = '';

            // Hide preview sections
            document.getElementById('file-preview-section').style.display = 'none';
            document.getElementById('video-preview').style.display = 'none';
            document.getElementById('other-file-preview').style.display = 'none';

            // Clear video source to stop playback
            const video = document.getElementById('preview-video');
            if (video.src) {
                URL.revokeObjectURL(video.src);
                video.src = '';
            }
        }

        // Image album management functions
        let newSelectedImages = [];
        let imagesToDelete = [];

        // Debug: Check if functions are loaded
        console.log('Image album functions loaded');
        console.log('removeCurrentImage function:', typeof removeCurrentImage);
        console.log('setCurrentImagePrimary function:', typeof setCurrentImagePrimary);

        // Test function to check if JavaScript is working
        function testJavaScript() {
            console.log('JavaScript is working!');
            alert('JavaScript is working!');
        }

        // Test delete function
        function testDeleteFunction() {
            console.log('Testing delete function...');

            // Test with a dummy ID
            const testId = 999;
            console.log('Testing with ID:', testId);

            // Add to arrays
            imagesToDelete.push(testId);
            filesToDelete.push(testId);

            // Add to global arrays
            window.deletedImages = window.deletedImages || [];
            window.deletedFiles = window.deletedFiles || [];
            window.deletedImages.push(testId);
            window.deletedFiles.push(testId);

            // Add hidden inputs
            addHiddenInput('deleted_images[]', testId);
            addHiddenInput('deleted_files[]', testId);

            console.log('Test completed. Arrays:', {
                imagesToDelete,
                filesToDelete,
                deletedImages: window.deletedImages,
                deletedFiles: window.deletedFiles
            });

            alert('Test delete function completed! Check console for details.');
        }

        // Submit form with deletions
        function submitFormWithDeletions(event) {
            console.log('submitFormWithDeletions called');

            // Check if we have any deletions
            const hasImageDeletions = (imagesToDelete.length > 0) || (window.deletedImages && window.deletedImages.length > 0);
            const hasFileDeletions = (filesToDelete.length > 0) || (window.deletedFiles && window.deletedFiles.length > 0);

            if (hasImageDeletions || hasFileDeletions) {
                console.log('We have deletions, preventing default submit and using AJAX...');
                event.preventDefault();

                // Create FormData
                const form = event.target;
                const formData = new FormData(form);

                // Add deletions
                if (imagesToDelete.length > 0) {
                    imagesToDelete.forEach(imageId => {
                        formData.append('deleted_images[]', imageId);
                    });
                }

                if (filesToDelete.length > 0) {
                    filesToDelete.forEach(fileId => {
                        formData.append('deleted_files[]', fileId);
                    });
                }

                if (window.deletedImages && window.deletedImages.length > 0) {
                    window.deletedImages.forEach(imageId => {
                        formData.append('deleted_images[]', imageId);
                    });
                }

                if (window.deletedFiles && window.deletedFiles.length > 0) {
                    window.deletedFiles.forEach(fileId => {
                        formData.append('deleted_files[]', fileId);
                    });
                }

                // Log final form data
                console.log('Final AJAX form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }

                // Submit via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => {
                        if (response.ok) {
                            console.log('Form submitted successfully');
                            window.location.href = response.url || '{{ route("admin.club-resources.index") }}';
                        } else {
                            console.error('Form submission failed');
                            alert('Có lỗi xảy ra khi cập nhật tài nguyên!');
                        }
                    })
                    .catch(error => {
                        console.error('AJAX error:', error);
                        alert('Có lỗi xảy ra khi cập nhật tài nguyên!');
                    });

                return false;
            }

            console.log('No deletions, proceeding with normal form submission');
            return true;
        }

        // Preview new images to be added
        function previewNewImages(input) {
            if (input.files && input.files.length > 0) {
                newSelectedImages = Array.from(input.files);
                displayNewImageAlbum();
            }
        }

        function displayNewImageAlbum() {
            const container = document.getElementById('new-images-preview-container');
            const previewSection = document.getElementById('new-images-preview-section');

            container.innerHTML = '';

            newSelectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-4 col-sm-6 mb-3';
                    col.innerHTML = `
                    <div class="card new-image-card" data-index="${index}">
                        <div class="card-body p-2">
                            <img src="${e.target.result}" class="img-fluid rounded" style="height: 150px; width: 100%; object-fit: cover;">
                            <div class="mt-2">
                                <small class="text-muted">${file.name}</small>
                                <br>
                                <small class="text-muted">${formatFileSize(file.size)}</small>
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeNewImageFromAlbum(${index})">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                    container.appendChild(col);
                };
                reader.readAsDataURL(file);
            });

            previewSection.style.display = 'block';
        }

        function removeNewImageFromAlbum(index) {
            newSelectedImages.splice(index, 1);

            // Update file input
            const dt = new DataTransfer();
            newSelectedImages.forEach(file => dt.items.add(file));
            document.getElementById('images').files = dt.files;

            displayNewImageAlbum();
        }

        // Remove current image
        function removeCurrentImage(imageId) {
            console.log('removeCurrentImage called with ID:', imageId);
            if (confirm('Bạn có chắc chắn muốn xóa hình ảnh này?')) {
                imagesToDelete.push(imageId);
                console.log('Added to imagesToDelete:', imagesToDelete);

                // Hide the image card
                const imageCard = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageCard) {
                    imageCard.closest('.col-md-4').style.display = 'none';
                    console.log('Image card hidden');
                } else {
                    console.log('Image card not found for ID:', imageId);
                }

                // Add hidden input to track deleted images
                addHiddenInput('deleted_images[]', imageId);

                // Also add to a global array for backup
                window.deletedImages = window.deletedImages || [];
                window.deletedImages.push(imageId);
                console.log('Global deletedImages:', window.deletedImages);
            }
        }

        // Set current image as primary
        function setCurrentImagePrimary(imageId) {
            console.log('setCurrentImagePrimary called with ID:', imageId);

            // Remove primary class from all current images
            document.querySelectorAll('.current-image-card').forEach(card => {
                card.classList.remove('border-primary');
                const badge = card.querySelector('.primary-badge');
                if (badge) badge.remove();
            });

            // Add primary class to selected image
            const selectedCard = document.querySelector(`[data-image-id="${imageId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('border-primary');

                // Add primary badge
                const cardBody = selectedCard.querySelector('.card-body');
                const badge = document.createElement('div');
                badge.className = 'primary-badge';
                badge.innerHTML = '<i class="fas fa-star"></i>';
                cardBody.appendChild(badge);
            }

            // Add hidden input to track primary image
            addHiddenInput('primary_image_id', imageId);
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // File album management functions
        let newSelectedFiles = [];
        let filesToDelete = [];

        // Debug: Check if functions are loaded
        console.log('File album functions loaded');
        console.log('removeCurrentFile function:', typeof removeCurrentFile);
        console.log('setCurrentFilePrimary function:', typeof setCurrentFilePrimary);

        function previewNewFiles(input) {
            if (input.files && input.files.length > 0) {
                newSelectedFiles = Array.from(input.files);
                displayNewFileAlbum();
            }
        }

        function displayNewFileAlbum() {
            const container = document.getElementById('new-files-preview-container');
            const previewSection = document.getElementById('new-files-preview-section');

            container.innerHTML = '';

            newSelectedFiles.forEach((file, index) => {
                const col = document.createElement('div');
                col.className = 'col-md-4 col-sm-6 mb-3';

                const fileIcon = getFileIcon(file.type);
                const fileSize = formatFileSize(file.size);

                col.innerHTML = `
                <div class="card new-file-card" data-index="${index}">
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
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeNewFileFromAlbum(${index})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="setNewFilePrimary(${index})">
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

        function removeNewFileFromAlbum(index) {
            newSelectedFiles.splice(index, 1);

            // Update file input
            const dt = new DataTransfer();
            newSelectedFiles.forEach(file => dt.items.add(file));
            document.getElementById('files').files = dt.files;

            displayNewFileAlbum();
        }

        function setNewFilePrimary(index) {
            // Remove primary class from all new files
            document.querySelectorAll('.new-file-card').forEach(card => {
                card.classList.remove('border-primary');
            });

            // Add primary class to selected file
            const selectedCard = document.querySelector(`[data-index="${index}"]`);
            if (selectedCard) {
                selectedCard.classList.add('border-primary');
            }
        }

        function removeCurrentFile(fileId) {
            console.log('removeCurrentFile called with ID:', fileId);
            if (confirm('Bạn có chắc chắn muốn xóa file này?')) {
                // Add to delete list
                if (!filesToDelete.includes(fileId)) {
                    filesToDelete.push(fileId);
                }
                console.log('Added to filesToDelete:', filesToDelete);

                // Hide the file card
                const fileCard = document.querySelector(`[data-file-id="${fileId}"]`);
                if (fileCard) {
                    fileCard.closest('.col-md-4').style.display = 'none';
                    console.log('File card hidden');
                } else {
                    console.log('File card not found for ID:', fileId);
                }

                // Add hidden input for deleted files
                addHiddenInput('deleted_files[]', fileId);

                // Also add to a global array for backup
                window.deletedFiles = window.deletedFiles || [];
                window.deletedFiles.push(fileId);
                console.log('Global deletedFiles:', window.deletedFiles);
            }
        }

        function setCurrentFilePrimary(fileId) {
            console.log('setCurrentFilePrimary called with ID:', fileId);

            // Remove primary from all current files
            document.querySelectorAll('.current-file-card').forEach(card => {
                card.classList.remove('border-primary');
            });

            // Set new primary
            const selectedCard = document.querySelector(`[data-file-id="${fileId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('border-primary');
            }

            // Add hidden input for primary file
            addHiddenInput('primary_file_id', fileId);
        }

        function addHiddenInput(name, value) {
            console.log('Adding hidden input:', name, value);

            // Remove existing input with same name
            const existingInput = document.querySelector(`input[name="${name}"][value="${value}"]`);
            if (existingInput) {
                existingInput.remove();
            }

            // Add new hidden input
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            document.querySelector('form').appendChild(input);

            console.log('Hidden input added:', input);

            // Verify the input was added
            const verifyInput = document.querySelector(`input[name="${name}"][value="${value}"]`);
            if (verifyInput) {
                console.log('✓ Hidden input verified:', verifyInput);
            } else {
                console.error('✗ Failed to add hidden input:', name, value);
            }
        }

        function getFileIcon(fileType) {
            if (fileType.includes('word') || fileType.includes('document')) return 'fas fa-file-word text-primary';
            if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'fas fa-file-excel text-success';

            return 'fas fa-file text-muted';
        }

        function debugFormData(event) {
            const formData = new FormData(event.target);
            console.log('Form data being submitted:');

            // Log all form data
            for (let [key, value] of formData.entries()) {
                console.log(key, ':', value);
            }

            // Specifically check for our hidden inputs
            const deletedFiles = formData.getAll('deleted_files[]');
            const deletedImages = formData.getAll('deleted_images[]');
            const primaryFileId = formData.get('primary_file_id');
            const primaryImageId = formData.get('primary_image_id');

            console.log('Deleted files:', deletedFiles);
            console.log('Deleted images:', deletedImages);
            console.log('Primary file ID:', primaryFileId);
            console.log('Primary image ID:', primaryImageId);

            // Check if we have any deletions to process
            if (deletedFiles.length === 0 && deletedImages.length === 0) {
                console.warn('WARNING: No files or images marked for deletion!');
                console.log('Current imagesToDelete array:', imagesToDelete);
                console.log('Current filesToDelete array:', filesToDelete);

                // Try to add deletions from arrays if they exist
                if (imagesToDelete.length > 0) {
                    console.log('Adding imagesToDelete to form...');
                    imagesToDelete.forEach(imageId => {
                        addHiddenInput('deleted_images[]', imageId);
                    });
                }

                if (filesToDelete.length > 0) {
                    console.log('Adding filesToDelete to form...');
                    filesToDelete.forEach(fileId => {
                        addHiddenInput('deleted_files[]', fileId);
                    });
                }

                // Also try global arrays as backup
                if (window.deletedImages && window.deletedImages.length > 0) {
                    console.log('Adding global deletedImages to form...');
                    window.deletedImages.forEach(imageId => {
                        addHiddenInput('deleted_images[]', imageId);
                    });
                }

                if (window.deletedFiles && window.deletedFiles.length > 0) {
                    console.log('Adding global deletedFiles to form...');
                    window.deletedFiles.forEach(fileId => {
                        addHiddenInput('deleted_files[]', fileId);
                    });
                }
            }

            // Force add deletions if needed
            console.log('=== FORCE ADDING DELETIONS ===');

            // Check if we have any deletions in our arrays
            const hasImageDeletions = (imagesToDelete.length > 0) || (window.deletedImages && window.deletedImages.length > 0);
            const hasFileDeletions = (filesToDelete.length > 0) || (window.deletedFiles && window.deletedFiles.length > 0);

            if (hasImageDeletions || hasFileDeletions) {
                console.log('Force adding deletions to form...');

                // Add image deletions
                if (imagesToDelete.length > 0) {
                    imagesToDelete.forEach(imageId => {
                        if (!document.querySelector(`input[name="deleted_images[]"][value="${imageId}"]`)) {
                            addHiddenInput('deleted_images[]', imageId);
                        }
                    });
                }

                if (window.deletedImages && window.deletedImages.length > 0) {
                    window.deletedImages.forEach(imageId => {
                        if (!document.querySelector(`input[name="deleted_images[]"][value="${imageId}"]`)) {
                            addHiddenInput('deleted_images[]', imageId);
                        }
                    });
                }

                // Add file deletions
                if (filesToDelete.length > 0) {
                    filesToDelete.forEach(fileId => {
                        if (!document.querySelector(`input[name="deleted_files[]"][value="${fileId}"]`)) {
                            addHiddenInput('deleted_files[]', fileId);
                        }
                    });
                }

                if (window.deletedFiles && window.deletedFiles.length > 0) {
                    window.deletedFiles.forEach(fileId => {
                        if (!document.querySelector(`input[name="deleted_files[]"][value="${fileId}"]`)) {
                            addHiddenInput('deleted_files[]', fileId);
                        }
                    });
                }
            }

            // Final check before submission
            console.log('=== FINAL CHECK BEFORE SUBMISSION ===');
            const allHiddenInputs = document.querySelectorAll('input[type="hidden"]');
            console.log('All hidden inputs:', allHiddenInputs);
            allHiddenInputs.forEach(input => {
                console.log(`Hidden input: ${input.name} = ${input.value}`);
            });

            // Force submit with deletions if needed
            if (hasImageDeletions || hasFileDeletions) {
                console.log('Forcing form submission with deletions...');

                // Create a new form data object
                const form = document.querySelector('form');
                const formData = new FormData(form);

                // Add deletions to form data
                if (imagesToDelete.length > 0) {
                    imagesToDelete.forEach(imageId => {
                        formData.append('deleted_images[]', imageId);
                    });
                }

                if (filesToDelete.length > 0) {
                    filesToDelete.forEach(fileId => {
                        formData.append('deleted_files[]', fileId);
                    });
                }

                if (window.deletedImages && window.deletedImages.length > 0) {
                    window.deletedImages.forEach(imageId => {
                        formData.append('deleted_images[]', imageId);
                    });
                }

                if (window.deletedFiles && window.deletedFiles.length > 0) {
                    window.deletedFiles.forEach(fileId => {
                        formData.append('deleted_files[]', fileId);
                    });
                }

                // Log the final form data
                console.log('Final form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
            }

            // Don't prevent form submission
            return true;
        }

        // Debug: Check if page is loaded
        console.log('Page loaded, checking elements...');
        console.log('Current images container:', document.getElementById('current-images-container'));
        console.log('Current files container:', document.getElementById('current-files-container'));

        // Alternative event binding method
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM loaded, binding events...');

            // Bind delete buttons for files
            document.querySelectorAll('[data-file-id]').forEach(function (card) {
                const fileId = card.getAttribute('data-file-id');
                const deleteBtn = card.querySelector('.btn-danger');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function () {
                        console.log('File delete button clicked via event listener for ID:', fileId);
                        removeCurrentFile(fileId);
                    });
                }
            });

            // Bind delete buttons for images
            document.querySelectorAll('[data-image-id]').forEach(function (card) {
                const imageId = card.getAttribute('data-image-id');
                const deleteBtn = card.querySelector('.btn-danger');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', function () {
                        console.log('Image delete button clicked via event listener for ID:', imageId);
                        removeCurrentImage(imageId);
                    });
                }
            });

            console.log('Event listeners bound successfully');
        });

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
        .current-image-card,
        .new-image-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .current-image-card:hover,
        .new-image-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .current-image-card.border-primary {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
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
            z-index: 10;
        }

        .current-image-card .card-body,
        .new-image-card .card-body {
            position: relative;
        }

        .current-image-card .btn,
        .new-image-card .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* File Album Styles */
        .current-file-card,
        .new-file-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .current-file-card:hover,
        .new-file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .current-file-card.border-primary,
        .new-file-card.border-primary {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .current-file-card .btn,
        .new-file-card .btn {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        #current-files-container .col-md-4,
        #new-files-preview-container .col-md-4 {
            margin-bottom: 1rem;
        }

        #current-images-container .col-md-4,
        #new-images-preview-container .col-md-4 {
            margin-bottom: 1rem;
        }
    </style>
@endsection

