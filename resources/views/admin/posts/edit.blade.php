@extends('admin.layouts.app')

@section('title', 'Chỉnh sửa bài viết')

@section('content')
<style>
/* Drag and Drop Styling for CKEditor */
.ck-editor__editable {
    transition: all 0.3s ease;
}


/* Image reordering styles */
.ck-editor__editable figure.image {
    cursor: move;
}

.ck-editor__editable figure.image.dragging {
    opacity: 0.5;
}

.ck-editor__editable figure.image.drag-over {
    border: 2px dashed #28a745;
}

/* Drop zone indicator */
.drop-indicator {
    height: 3px;
    background: #007bff;
    margin: 5px 0;
}

.drop-indicator.show {
    opacity: 1;
}

</style>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> 
            <strong>Có lỗi xảy ra:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                    <form method="POST" action="{{ route('admin.posts.update', $post->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $post->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content">{{ old('content', $post->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    
                                    @if($post->image)
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle"></i> 
                                                Hình ảnh hiện tại: <strong>{{ $post->image }}</strong>
                                            </small>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
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
                                    
                                    <!-- Current Image Display -->
                                    @if($post->image)
                                        <div class="current-image-section">
                                            <h6>Hình ảnh hiện tại:</h6>
                                            <div class="text-center">
                                                <img src="{{ asset('storage/' . $post->image) }}" 
                                                     alt="Current image" 
                                                     class="img-thumbnail" 
                                                     style="max-width: 200px; max-height: 200px;">
                                                <div class="mt-2">
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteCurrentImage()">
                                                        <i class="fas fa-trash"></i> Xóa ảnh này
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p>Chưa có hình ảnh</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select @error('club_id') is-invalid @enderror" name="club_id" required>
                                        <option value="">Chọn câu lạc bộ</option>
                                        @foreach($clubs as $club)
                                            <option value="{{ $club->id }}" {{ old('club_id', $post->club_id) == $club->id ? 'selected' : '' }}>
                                                {{ $club->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('club_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" name="type" required>
                                        <option value="post" {{ old('type', $post->type) == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                                        <option value="announcement" {{ old('type', $post->type) == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Công khai</option>
                                <option value="hidden" {{ old('status', $post->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                <option value="deleted" {{ old('status', $post->status) == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
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

<!-- CKEditor Script -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
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
                'image', '|',
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
                    
                    // Add visual feedback
                    e.target.classList.add('drag-over');
                    
                    // Create drop indicator
                    if (!dropIndicator) {
                        dropIndicator = document.createElement('div');
                        dropIndicator.className = 'drop-indicator';
                        dropIndicator.style.position = 'absolute';
                        dropIndicator.style.left = '0';
                        dropIndicator.style.right = '0';
                        dropIndicator.style.zIndex = '1000';
                    }
                    
                    // Position indicator
                    const rect = e.target.getBoundingClientRect();
                    const editorRect = editorElement.getBoundingClientRect();
                    const relativeY = rect.top - editorRect.top;
                    
                    if (relativeY < rect.height / 2) {
                        // Insert before
                        e.target.parentNode.insertBefore(dropIndicator, e.target);
                    } else {
                        // Insert after
                        e.target.parentNode.insertBefore(dropIndicator, e.target.nextSibling);
                    }
                    
                    dropIndicator.classList.add('show');
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
                    
                    // Remove drop indicator
                    if (dropIndicator) {
                        dropIndicator.remove();
                        dropIndicator = null;
                    }
                    
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
            
            // Custom image upload handler
            editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
                return {
                    upload: function() {
                        return new Promise(function(resolve, reject) {
                            // Create file input
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/*';
                            
                            input.onchange = function(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        // Create image element with styling
                                        const imageHtml = `<figure class="image">
                                            <img src="${e.target.result}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                            <figcaption style="text-align: center; font-style: italic; color: #666; margin-top: 8px;">${file.name}</figcaption>
                                        </figure>`;
                                        
                                        resolve({
                                            default: e.target.result
                                        });
                                        
                                        // Show success message
                                        showSuccessMessage(`Hình ảnh "${file.name}" đã được thêm vào nội dung!`);
                                    };
                                    reader.readAsDataURL(file);
                                } else {
                                    reject('Không có file được chọn');
                                }
                            };
                            
                            input.click();
                        });
                    }
                };
            };
            
            // Add form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const content = editor.getData();
                if (!content || content.trim() === '') {
                    e.preventDefault();
                    alert('Vui lòng nhập nội dung bài viết!');
                    return false;
                }
                
                // Debug: Log form data before submission
                const formData = new FormData(form);
                console.log('Form submission data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, ':', value);
                }
            });
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Global variables for selected images
    window.selectedImages = [];
    window.editor = null;


    // Multiple images preview functions
    window.previewImages = function(input) {
        if (input.files && input.files.length > 0) {
            window.selectedImages = Array.from(input.files);
            displaySelectedImages();
            
            // Enable "Add all to editor" button
            document.getElementById('add-all-to-editor-btn').disabled = false;
            
            // Hide current image if exists
            const currentImage = document.getElementById('current-image');
            if (currentImage) {
                currentImage.style.display = 'none';
            }
        }
    };

    window.displaySelectedImages = function() {
        const container = document.getElementById('images-preview-container');
        const preview = document.getElementById('images-preview');
        const addAllBtn = document.getElementById('add-all-to-editor-btn');
        
        preview.innerHTML = '';
        
        if (window.selectedImages.length === 0) {
            container.style.display = 'none';
            if (addAllBtn) {
                addAllBtn.disabled = true;
            }
            return;
        }
        
        container.style.display = 'block';
        if (addAllBtn) {
            addAllBtn.disabled = false;
        }
        
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
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    };

    window.removeSelectedImage = function(index) {
        window.selectedImages.splice(index, 1);
        
        if (window.selectedImages.length === 0) {
            document.getElementById('images-preview-container').style.display = 'none';
            document.getElementById('add-all-to-editor-btn').disabled = true;
            
            // Show current image if exists
            const currentImage = document.getElementById('current-image');
            if (currentImage) {
                currentImage.style.display = 'block';
            }
        } else {
            displaySelectedImages();
        }
    };

    window.clearAllImages = function() {
        window.selectedImages = [];
        document.getElementById('images').value = '';
        document.getElementById('images-preview-container').style.display = 'none';
        document.getElementById('add-all-to-editor-btn').disabled = true;
        
        // Show current image if exists
        const currentImage = document.getElementById('current-image');
        if (currentImage) {
            currentImage.style.display = 'block';
        }
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
                
                // Update preview display
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

    window.deleteCurrentImage = function() {
        if (confirm('Bạn có chắc chắn muốn xóa ảnh hiện tại? Hành động này không thể hoàn tác.')) {
            // Hide current image
            const currentImage = document.getElementById('current-image');
            if (currentImage) {
                currentImage.style.display = 'none';
            }
            
            // Show no-image placeholder
            const noImage = document.getElementById('no-image');
            if (noImage) {
                noImage.style.display = 'block';
            }
            
            // Remove any existing delete input first
            const existingDeleteInput = document.querySelector('input[name="delete_current_image"]');
            if (existingDeleteInput) {
                existingDeleteInput.remove();
            }
            
            // Add hidden input to mark image for deletion
            const form = document.querySelector('form');
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_current_image';
            deleteInput.value = '1';
            form.appendChild(deleteInput);
            
            console.log('Delete input added:', deleteInput);
            showSuccessMessage('Ảnh hiện tại đã được đánh dấu để xóa. Nhấn "Cập nhật" để xác nhận.');
        }
    };
});
</script>
@endsection

