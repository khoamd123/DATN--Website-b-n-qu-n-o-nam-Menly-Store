@extends('admin.layouts.app')

@section('title', 'Tạo bài viết mới')

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

/* Image resize styles */
.ck-editor__editable figure.image {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.ck-editor__editable figure.image:hover {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.ck-editor__editable figure.image .resize-handles {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.ck-editor__editable figure.image:hover .resize-handles {
    pointer-events: all;
}

.resize-handle {
    position: absolute;
    background: #007bff;
    border: 2px solid white;
    border-radius: 50%;
    width: 12px;
    height: 12px;
    cursor: pointer;
}

.resize-handle.nw { top: -6px; left: -6px; cursor: nw-resize; }
.resize-handle.ne { top: -6px; right: -6px; cursor: ne-resize; }
.resize-handle.sw { bottom: -6px; left: -6px; cursor: sw-resize; }
.resize-handle.se { bottom: -6px; right: -6px; cursor: se-resize; }
.resize-handle.n { top: -6px; left: 50%; transform: translateX(-50%); cursor: n-resize; }
.resize-handle.s { bottom: -6px; left: 50%; transform: translateX(-50%); cursor: s-resize; }
.resize-handle.w { top: 50%; left: -6px; transform: translateY(-50%); cursor: w-resize; }
.resize-handle.e { top: 50%; right: -6px; transform: translateY(-50%); cursor: e-resize; }

/* Image size controls */
.image-size-controls {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    display: none;
}

.ck-editor__editable figure.image:hover .image-size-controls {
    display: block;
}

.size-btn {
    background: transparent;
    border: 1px solid white;
    color: white;
    padding: 2px 6px;
    margin: 0 2px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 10px;
}

.size-btn:hover {
    background: white;
    color: black;
}

</style>
<div class="container-fluid">
    <div class="content-header">
        <h1><i class="fas fa-plus-circle"></i> Tạo bài viết mới</h1>
    <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Quản lý bài viết</a></li>
                <li class="breadcrumb-item active">Tạo bài viết</li>
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
                    <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">
                    @csrf
                        
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
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <textarea class="form-control" id="content" name="content">{{ old('content') }}</textarea>
                                <div class="drop-zone-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,123,255,0.1); border: 2px dashed #007bff; border-radius: 4px; display: none; align-items: center; justify-content: center; z-index: 1000;">
                                    <div class="text-center text-primary">
                                        <i class="fas fa-cloud-upload-alt fa-3x mb-2"></i>
                                        <h5>Thả hình ảnh vào đây để thêm vào nội dung</h5>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Bạn có thể kéo thả hình ảnh trực tiếp vào vùng soạn thảo hoặc sử dụng nút "Thêm vào nội dung" ở dưới. Sau khi thêm ảnh, hover vào ảnh để chỉnh kích thước.
                            </small>
                        </div>
                
                        <div class="row">
                            <div class="col-md-6">
                    <div class="mb-3">
                                    <label class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                                    <select class="form-select" name="club_id" required>
                            <option value="">Chọn câu lạc bộ</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ old('club_id') == $club->id ? 'selected' : '' }}>
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
                                        <option value="post" {{ old('type') == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                            <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                        </select>
                                </div>
                            </div>
                    </div>

                    <div class="mb-3">
                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" required>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Công khai</option>
                            <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                <option value="deleted" {{ old('status') == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
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
                                <div id="no-image" class="text-center text-muted p-3" style="border: 2px dashed #dee2e6; border-radius: 8px;">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <p class="mb-0">Chưa có hình ảnh</p>
                                </div>
                            </div>
                        </div>
                    </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo bài viết
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
            
            // Add form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const content = editor.getData();
                if (!content || content.trim() === '') {
                    e.preventDefault();
                    alert('Vui lòng nhập nội dung bài viết!');
                    return false;
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
            document.getElementById('no-image').style.display = 'block';
            document.getElementById('add-all-to-editor-btn').disabled = true;
            return;
        }
        
        previewContainer.style.display = 'block';
        document.getElementById('no-image').style.display = 'none';
        
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
                const imageHtml = `<figure class="image" data-image-id="${Date.now()}">
                    <img src="${e.target.result}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <figcaption style="text-align: center; font-style: italic; color: #666; margin-top: 8px;">${file.name}</figcaption>
                    <div class="resize-handles">
                        <div class="resize-handle nw"></div>
                        <div class="resize-handle ne"></div>
                        <div class="resize-handle sw"></div>
                        <div class="resize-handle se"></div>
                        <div class="resize-handle n"></div>
                        <div class="resize-handle s"></div>
                        <div class="resize-handle w"></div>
                        <div class="resize-handle e"></div>
                    </div>
                    <div class="image-size-controls">
                        <button class="size-btn" onclick="setImageSize(this, '25%')">25%</button>
                        <button class="size-btn" onclick="setImageSize(this, '50%')">50%</button>
                        <button class="size-btn" onclick="setImageSize(this, '75%')">75%</button>
                        <button class="size-btn" onclick="setImageSize(this, '100%')">100%</button>
                    </div>
                </figure>`;
                
                window.editor.model.change(writer => {
                    const viewFragment = window.editor.data.processor.toView(imageHtml);
                    const modelFragment = window.editor.data.toModel(viewFragment);
                    
                    // Move to the end of the document
                    const root = window.editor.model.document.getRoot();
                    const lastPosition = writer.createPositionAt(root, 'end');
                    window.editor.model.insertContent(modelFragment, lastPosition);
                });
                
                // Add resize functionality after image is inserted
                setTimeout(() => {
                    addImageResizeFunctionality();
                }, 100);
                
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
                    const imageHtml = `<figure class="image" data-image-id="${Date.now()}">
                        <img src="${e.target.result}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <figcaption style="text-align: center; font-style: italic; color: #666; margin-top: 8px;">${file.name}</figcaption>
                        <div class="resize-handles">
                            <div class="resize-handle nw"></div>
                            <div class="resize-handle ne"></div>
                            <div class="resize-handle sw"></div>
                            <div class="resize-handle se"></div>
                            <div class="resize-handle n"></div>
                            <div class="resize-handle s"></div>
                            <div class="resize-handle w"></div>
                            <div class="resize-handle e"></div>
                        </div>
                        <div class="image-size-controls">
                            <button class="size-btn" onclick="setImageSize(this, '25%')">25%</button>
                            <button class="size-btn" onclick="setImageSize(this, '50%')">50%</button>
                            <button class="size-btn" onclick="setImageSize(this, '75%')">75%</button>
                            <button class="size-btn" onclick="setImageSize(this, '100%')">100%</button>
                        </div>
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
                        
                        // Add resize functionality after all images are inserted
                        setTimeout(() => {
                            addImageResizeFunctionality();
                        }, 100);
                        
                        // Clear all selected images after adding to editor
                        window.selectedImages = [];
                        displaySelectedImages();
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    };

    // Image resize functionality
    window.addImageResizeFunctionality = function() {
        const editorElement = window.editor.ui.getEditableElement();
        const images = editorElement.querySelectorAll('figure.image');
        
        images.forEach(image => {
            // Add resize handles if not already present
            if (!image.querySelector('.resize-handles')) {
                const resizeHandles = document.createElement('div');
                resizeHandles.className = 'resize-handles';
                resizeHandles.innerHTML = `
                    <div class="resize-handle nw"></div>
                    <div class="resize-handle ne"></div>
                    <div class="resize-handle sw"></div>
                    <div class="resize-handle se"></div>
                    <div class="resize-handle n"></div>
                    <div class="resize-handle s"></div>
                    <div class="resize-handle w"></div>
                    <div class="resize-handle e"></div>
                `;
                image.appendChild(resizeHandles);
            }
            
            // Add size controls if not already present
            if (!image.querySelector('.image-size-controls')) {
                const sizeControls = document.createElement('div');
                sizeControls.className = 'image-size-controls';
                sizeControls.innerHTML = `
                    <button class="size-btn" onclick="setImageSize(this, '25%')">25%</button>
                    <button class="size-btn" onclick="setImageSize(this, '50%')">50%</button>
                    <button class="size-btn" onclick="setImageSize(this, '75%')">75%</button>
                    <button class="size-btn" onclick="setImageSize(this, '100%')">100%</button>
                `;
                image.appendChild(sizeControls);
            }
        });
    };

    // Set image size function
    window.setImageSize = function(button, size) {
        const image = button.closest('figure.image');
        const img = image.querySelector('img');
        
        if (img) {
            img.style.width = size;
            img.style.height = 'auto';
            showSuccessMessage(`Đã thay đổi kích thước ảnh thành ${size}`);
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

});
</script>
@endsection
