@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa bài viết')

@section('content')
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-edit"></i> Chỉnh sửa bài viết</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Quản lý bài viết</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa bài viết</li>
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
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Nội dung bài viết</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.posts.update', $post->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $post->title) }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>

                            <textarea class="form-control" id="content" name="content">{{ old('content', $post->content) }}</textarea>

                        </div>
                
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                                        <option value="">Chọn câu lạc bộ</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id', $post->club_id) == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                                    <select class="form-select" name="type" required>
                                        <option value="post" {{ old('type', $post->type) == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                                        <option value="announcement" {{ old('type', $post->type) == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Công khai</option>
                                <option value="members_only" {{ old('status', $post->status) == 'members_only' ? 'selected' : '' }}>Chỉ thành viên CLB</option>
                                <option value="hidden" {{ old('status', $post->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                <option value="deleted" {{ old('status', $post->status) == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                            </select>
                        </div>

                        <!-- Multiple Image Upload Section -->
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh bài viết</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple onchange="previewImages(this)">
                                    <small class="form-text text-muted">Chọn nhiều hình ảnh cùng lúc (JPG, PNG, GIF)</small>
                                    
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('images').click()">
                                            <i class="fas fa-upload"></i> Chọn nhiều ảnh
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="addAllImagesToEditor()" disabled id="add-all-to-editor-btn">
                                            <i class="fas fa-plus"></i> Thêm tất cả vào nội dung
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Current Images Gallery -->
                                    @if($post->attachments && $post->attachments->count() > 0)
                                        <div class="mb-3" id="current-images-section">
                                            <h6>Thư viện ảnh hiện tại ({{ $post->attachments->count() }} ảnh):</h6>
                                            <div class="row g-2" id="current-images-gallery">
                                                @foreach($post->attachments as $index => $attachment)
                                                    <div class="col-md-6 col-sm-6 mb-2 current-image-item" data-attachment-id="{{ $attachment->id }}">
                                                        <div style="position: relative;">
                                                            <img src="{{ asset($attachment->file_url) }}" alt="Ảnh {{ $index + 1 }}" 
                                                                 class="img-fluid rounded border" 
                                                                 style="height: 120px; width: 100%; object-fit: cover;">
                                                            <button type="button" class="btn btn-sm btn-danger" 
                                                                    onclick="removeAttachmentImage({{ $attachment->id }})" 
                                                                    style="position: absolute; top: 5px; right: 5px; z-index: 10;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="deleted_attachments" id="deleted_attachments" value="">
                                        </div>
                                    @elseif($post->image)
                                        <div class="mb-3" id="current-image-section">
                                            <h6>Ảnh hiện tại:</h6>
                                            <div style="position: relative; display: inline-block;">
                                                <img src="{{ asset($post->image) }}" alt="Current image" style="max-width: 200px; border-radius: 8px; border: 2px solid #dee2e6;">
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeCurrentImage()" style="position: absolute; top: 5px; right: 5px;">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                            <input type="hidden" name="remove_image" id="remove_image" value="0">
                                        </div>
                                    @endif
                                    
                                    <!-- Selected Images Preview -->
                                    <div id="images-preview-container" class="mb-3" style="display: none;">
                                        <h6>Ảnh đã chọn:</h6>
                                        <div id="images-preview" class="row"></div>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="clearAllImages()">
                                                <i class="fas fa-trash"></i> Xóa tất cả
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- No Image Placeholder -->
                                    <div id="no-image" class="text-center text-muted p-3" style="border: 2px dashed #dee2e6; border-radius: 8px; display: {{ ($post->attachments && $post->attachments->count() > 0) || $post->image ? 'none' : 'block' }};">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <p class="mb-0">Chưa có hình ảnh</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật bài viết
                            </button>
                            <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    ClassicEditor
        .create(document.querySelector('#content'), {
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
            console.log('CKEditor initialized successfully');
            
            // Store editor reference globally
            window.editor = editor;
            
            // Add drag and drop functionality to CKEditor
            const editorElement = editor.ui.getEditableElement();
            
            // Variables for image reordering
            let draggedImage = null;
            let dropIndicator = null;
            
            // Add image reordering functionality
            editorElement.addEventListener('dragstart', function(e) {
                if (e.target.tagName === 'FIGURE' && e.target.classList.contains('image')) {
                    draggedImage = e.target;
                    e.target.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', e.target.outerHTML);
                }
            });
            
            editorElement.addEventListener('dragend', function(e) {
                if (e.target.tagName === 'FIGURE' && e.target.classList.contains('image')) {
                    e.target.classList.remove('dragging');
                    draggedImage = null;
                    // Remove all drop indicators
                    document.querySelectorAll('.drop-indicator').forEach(indicator => {
                        indicator.remove();
                    });
                }
            });
            
            editorElement.addEventListener('dragover', function(e) {
                if (draggedImage && e.target.tagName === 'FIGURE' && e.target.classList.contains('image')) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    e.target.classList.add('drag-over');
                }
            });
            
            editorElement.addEventListener('dragleave', function(e) {
                if (e.target.tagName === 'FIGURE' && e.target.classList.contains('image')) {
                    e.target.classList.remove('drag-over');
                }
            });
            
            editorElement.addEventListener('drop', function(e) {
                if (draggedImage && e.target.tagName === 'FIGURE' && e.target.classList.contains('image')) {
                    e.preventDefault();
                    e.target.classList.remove('drag-over');
                    
                    // Move the image
                    const rect = e.target.getBoundingClientRect();
                    const editorRect = editorElement.getBoundingClientRect();
                    const relativeY = rect.top - editorRect.top;
                    
                    if (relativeY < rect.height / 2) {
                        // Insert before
                        e.target.parentNode.insertBefore(draggedImage, e.target);
                    } else {
                        // Insert after
                        e.target.parentNode.insertBefore(draggedImage, e.target.nextSibling);
                    }
                    
                    showSuccessMessage('Đã sắp xếp lại vị trí ảnh!');
                }
            });
            
            // Add form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const content = editor.getData();
                const title = document.querySelector('input[name="title"]').value.trim();
                
                if (!title) {
                    e.preventDefault();
                    alert('Vui lòng nhập tiêu đề bài viết!');
                    return false;
                }
                
                if (!content || content.trim() === '' || content === '<p></p>') {
                    e.preventDefault();
                    alert('Vui lòng nhập nội dung bài viết!');
                    return false;
                }
                
                // Sync content from CKEditor to textarea
                document.getElementById('content').value = content;
                
                // Thêm loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
            });

        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Global variables for selected images
    window.selectedImages = [];

    // Multiple images preview functions
    window.previewImages = function(input) {
        if (input.files && input.files.length > 0) {
            window.selectedImages = Array.from(input.files);
            displaySelectedImages();
            
            // Enable "Add all to editor" button
            document.getElementById('add-all-to-editor-btn').disabled = false;
            
            // Hide no-image placeholder
            document.getElementById('no-image').style.display = 'none';
        }
    };

    window.displaySelectedImages = function() {
        const container = document.getElementById('images-preview');
        const previewContainer = document.getElementById('images-preview-container');
        
        container.innerHTML = '';
        
        if (window.selectedImages.length === 0) {
            previewContainer.style.display = 'none';
            document.getElementById('add-all-to-editor-btn').disabled = true;
            return;
        }
        
        previewContainer.style.display = 'block';
        
        window.selectedImages.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-2';
                col.innerHTML = `
                    <div class="card" style="position: relative;">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="card-text text-truncate d-block" title="${file.name}">${file.name}</small>
                            <div class="btn-group btn-group-sm mt-1" role="group">
                                <button type="button" class="btn btn-success btn-sm" onclick="addSingleImageToEditor(${index})" title="Thêm vào nội dung">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeSelectedImage(${index})" title="Xóa ảnh">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    };

    window.removeSelectedImage = function(index) {
        window.selectedImages.splice(index, 1);
        displaySelectedImages();
    };

    window.clearAllImages = function() {
        window.selectedImages = [];
        document.getElementById('images').value = '';
        displaySelectedImages();
    };

    window.addSingleImageToEditor = function(index) {
        if (window.editor && window.selectedImages[index]) {
            const file = window.selectedImages[index];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const imageHtml = `<figure class="image">
                    <img src="${e.target.result}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <figcaption style="text-align: center; font-style: italic; color: #666; margin-top: 8px;">${file.name}</figcaption>
                </figure>`;
                
                window.editor.model.change(writer => {
                    const viewFragment = window.editor.data.processor.toView(imageHtml);
                    const modelFragment = window.editor.data.toModel(viewFragment);
                    
                    // Move to the end of the document
                    const root = window.editor.model.document.getRoot();
                    const lastPosition = writer.createPositionAt(root, 'end');
                    window.editor.model.insertContent(modelFragment, lastPosition);
                });
                
                showSuccessMessage(`Hình ảnh "${file.name}" đã được thêm vào nội dung!`);
                
                // Remove image from selected images array
                window.selectedImages.splice(index, 1);
                displaySelectedImages();
            };
            reader.readAsDataURL(file);
        }
    };

    window.addAllImagesToEditor = function() {
        if (window.editor && window.selectedImages.length > 0) {
            let addedCount = 0;
            
            window.selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageHtml = `<figure class="image">
                        <img src="${e.target.result}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <figcaption style="text-align: center; font-style: italic; color: #666; margin-top: 8px;">${file.name}</figcaption>
                    </figure>`;
                    
                    window.editor.model.change(writer => {
                        const viewFragment = window.editor.data.processor.toView(imageHtml);
                        const modelFragment = window.editor.data.toModel(viewFragment);
                        
                        // Move to the end of the document
                        const root = window.editor.model.document.getRoot();
                        const lastPosition = writer.createPositionAt(root, 'end');
                        window.editor.model.insertContent(modelFragment, lastPosition);
                    });
                    
                    addedCount++;
                    if (addedCount === window.selectedImages.length) {
                        showSuccessMessage(`Đã thêm ${addedCount} hình ảnh vào nội dung!`);
                        
                        // Clear all selected images after adding to editor
                        window.selectedImages = [];
                        displaySelectedImages();
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    };

    window.showSuccessMessage = function(message) {
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success alert-dismissible fade show';
        successAlert.style.position = 'fixed';
        successAlert.style.top = '20px';
        successAlert.style.right = '20px';
        successAlert.style.zIndex = '9999';
        successAlert.innerHTML = `
            <i class="fas fa-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(successAlert);
        
        setTimeout(() => {
            if (successAlert.parentNode) {
                successAlert.remove();
            }
        }, 3000);
    };

    // Mảng lưu các attachment IDs đã đánh dấu xóa
    window.deletedAttachmentIds = [];

    // Xóa ảnh attachment theo ID
    window.removeAttachmentImage = function(attachmentId) {
        if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
            // Thêm ID vào danh sách xóa
            if (!window.deletedAttachmentIds.includes(attachmentId)) {
                window.deletedAttachmentIds.push(attachmentId);
            }
            
            // Ẩn ảnh trong gallery
            const imageItem = document.querySelector(`.current-image-item[data-attachment-id="${attachmentId}"]`);
            if (imageItem) {
                imageItem.style.display = 'none';
            }
            
            // Cập nhật hidden input
            updateDeletedAttachmentsInput();
            
            // Kiểm tra nếu không còn ảnh nào thì hiển thị placeholder
            checkAndShowPlaceholder();
            
            showSuccessMessage('Ảnh đã được đánh dấu xóa. Nhấn "Cập nhật bài viết" để xác nhận.');
        }
    };

    // Cập nhật hidden input với danh sách IDs đã xóa
    function updateDeletedAttachmentsInput() {
        const deletedAttachmentsInput = document.getElementById('deleted_attachments');
        if (deletedAttachmentsInput) {
            deletedAttachmentsInput.value = window.deletedAttachmentIds.join(',');
        }
    }

    // Kiểm tra và hiển thị placeholder nếu không còn ảnh
    function checkAndShowPlaceholder() {
        const currentImagesSection = document.getElementById('current-images-section');
        const currentImageSection = document.getElementById('current-image-section');
        const noImagePlaceholder = document.getElementById('no-image');
        
        if (currentImagesSection) {
            const visibleImages = currentImagesSection.querySelectorAll('.current-image-item[style*="display: none"]').length;
            const totalImages = currentImagesSection.querySelectorAll('.current-image-item').length;
            
            if (visibleImages === totalImages && totalImages > 0) {
                // Tất cả ảnh đã được đánh dấu xóa
                if (noImagePlaceholder) {
                    noImagePlaceholder.style.display = 'block';
                }
            }
        } else if (currentImageSection && currentImageSection.style.display === 'none') {
            if (noImagePlaceholder) {
                noImagePlaceholder.style.display = 'block';
            }
        }
    }

    // Xóa ảnh hiện tại (trường hợp chỉ có ảnh trong cột image, không có attachments)
    window.removeCurrentImage = function() {
        if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
            // Ẩn phần hiển thị ảnh hiện tại
            const currentImageSection = document.getElementById('current-image-section');
            if (currentImageSection) {
                currentImageSection.style.display = 'none';
            }
            
            // Hiển thị placeholder "Chưa có hình ảnh"
            const noImagePlaceholder = document.getElementById('no-image');
            if (noImagePlaceholder) {
                noImagePlaceholder.style.display = 'block';
            }
            
            // Đặt giá trị hidden input để xóa ảnh
            const removeImageInput = document.getElementById('remove_image');
            if (removeImageInput) {
                removeImageInput.value = '1';
            }
            
            showSuccessMessage('Ảnh đã được đánh dấu xóa. Nhấn "Cập nhật bài viết" để xác nhận.');
        }
    };

});
</script>
@endsection

