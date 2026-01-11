@extends('admin.layouts.app')

@section('title', 'Tạo bài viết mới')

@section('content')
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
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-2">
                                    <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" id="post-title" value="{{ old('title') }}" required>
                                </div>

                                <!-- Permalink (slug) preview like WordPress -->
                                <div class="mb-3">
                                    <small class="text-muted">
                                        Liên kết tĩnh: <span class="me-1">{{ url('posts') }}/</span>
                                        <span id="slug-preview" class="fw-semibold">{{ \Illuminate\Support\Str::slug(old('title', 'viet-tieu-de-tai-day')) }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="edit-slug-btn">Chỉnh sửa</button>
                                    </small>
                                    <div id="slug-edit-row" class="mt-2" style="display:none;">
                                        <div class="input-group input-group-sm" style="max-width: 420px;">
                                            <span class="input-group-text">{{ url('posts') }}/</span>
                                            <input type="text" class="form-control" name="slug" id="slug-input" value="{{ old('slug') ?? \Illuminate\Support\Str::slug(old('title')) }}">
                                            <button type="button" class="btn btn-primary" id="save-slug-btn">OK</button>
                                            <button type="button" class="btn btn-outline-secondary" id="cancel-slug-btn">Hủy</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Toolbar row: Media button (like WordPress) -->
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <button type="button" id="add-media-btn" class="btn btn-sm btn-outline-primary" disabled>
                                        <i class="fas fa-photo-video"></i> Thêm Media
                                    </button>
                                    <input type="file" id="quick-media-input" accept="image/*" multiple style="display:none">
                                    <small class="text-muted">Nhấp Thêm Media để chọn ảnh và chèn trực tiếp.</small>
                                </div>
                                <style>
                                    #add-media-btn:disabled {
                                        opacity: 0.6;
                                        cursor: not-allowed;
                                    }
                                </style>

                                <div class="mb-3">
                                    <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="10">{{ old('content') }}</textarea>
                                </div>

                                <!-- Bỏ thư viện ảnh xem trước để đơn giản hóa quy trình chèn ảnh -->
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-3">
                                    <div class="card-header"><strong>Đăng bài viết</strong></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-select" name="status" required>
                                                <option value="published" {{ old('status','published') == 'published' ? 'selected' : '' }}>Công khai</option>
                                                <option value="members_only" {{ old('status') == 'members_only' ? 'selected' : '' }}>Chỉ thành viên CLB</option>
                                                <option value="hidden" {{ old('status') == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                                <option value="deleted" {{ old('status') == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-save"></i> Tạo bài viết
                                            </button>
                                            <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-3">
                                    <div class="card-header"><strong>Chuyên mục</strong></div>
                                    <div class="card-body">
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
                                        <div class="mb-0">
                                            <label class="form-label">Loại bài viết</label>
                                            <select class="form-select" name="type" required>
                                                <option value="post" {{ old('type') == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                                                <option value="announcement" {{ old('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header"><strong>Ảnh đại diện</strong></div>
                                    <div class="card-body">
                                        <input type="file" class="form-control mb-2" id="featured-image" name="image" accept="image/*">
                                        <div id="featured-preview" class="text-center" style="display:none;">
                                            <img id="featured-img" src="#" alt="Ảnh đại diện" class="img-fluid rounded border" style="max-height: 200px; object-fit: cover;">
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="remove-featured">Xóa ảnh</button>
                                            </div>
                                        </div>
                                        <div id="featured-placeholder" class="text-center text-muted p-3" style="border: 2px dashed #dee2e6; border-radius: 8px;">
                                            <i class="fas fa-image fa-2x mb-2"></i>
                                            <p class="mb-0">Chưa chọn ảnh đại diện</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('partials.ckeditor-upload-adapter', ['uploadUrl' => route('admin.posts.upload-image'), 'csrfToken' => csrf_token()])
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tạo upload adapter plugin
    const SimpleUploadAdapterPlugin = window.CKEditorUploadAdapterFactory('{{ route("admin.posts.upload-image") }}', '{{ csrf_token() }}');
    
    ClassicEditor
        .create(document.querySelector('#content'), {
            extraPlugins: [SimpleUploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', '|',
                    'link', 'uploadImage', '|',
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
        })
        .then(editor => {
            console.log('CKEditor initialized successfully');
            
            // Store editor reference globally
            window.editor = editor;
            
            // Enable media button now that editor is ready
            const addMediaBtn = document.getElementById('add-media-btn');
            if (addMediaBtn) {
                addMediaBtn.disabled = false;
                addMediaBtn.title = 'Thêm ảnh vào bài viết';
            }
            
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
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
            });

        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
            alert('Không thể khởi tạo trình soạn thảo. Vui lòng tải lại trang.');
            
            // Keep button disabled if editor fails to initialize
            const addMediaBtn = document.getElementById('add-media-btn');
            if (addMediaBtn) {
                addMediaBtn.disabled = true;
                addMediaBtn.title = 'Lỗi khởi tạo editor';
            }
        });

    // Global variables for selected images
    window.selectedImages = [];

    // WordPress-like slug + media behaviors
    const slugify = (str) => {
        return (str || '')
            .toString()
            .normalize('NFD').replace(/\p{Diacritic}/gu, '')
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    const titleInput = document.getElementById('post-title');
    const slugPreview = document.getElementById('slug-preview');
    const slugEditRow = document.getElementById('slug-edit-row');
    const slugInput = document.getElementById('slug-input');
    const editSlugBtn = document.getElementById('edit-slug-btn');
    const saveSlugBtn = document.getElementById('save-slug-btn');
    const cancelSlugBtn = document.getElementById('cancel-slug-btn');

    if (titleInput && slugPreview) {
        titleInput.addEventListener('input', function() {
            if (slugEditRow && slugEditRow.style.display !== 'block') {
                const s = slugify(this.value);
                slugPreview.textContent = s || 'viet-tieu-de-tai-day';
                if (slugInput) slugInput.value = s;
            }
        });
    }

    if (editSlugBtn) {
        editSlugBtn.addEventListener('click', function() {
            slugEditRow.style.display = 'block';
            slugInput.focus();
        });
    }
    if (saveSlugBtn) {
        saveSlugBtn.addEventListener('click', function() {
            const s = slugify(slugInput.value);
            slugPreview.textContent = s || 'viet-tieu-de-tai-day';
            slugInput.value = s;
            slugEditRow.style.display = 'none';
        });
    }
    if (cancelSlugBtn) {
        cancelSlugBtn.addEventListener('click', function() {
            slugEditRow.style.display = 'none';
        });
    }

    // Media button: choose files and insert immediately
    const addMediaBtn = document.getElementById('add-media-btn');
    const quickMediaInput = document.getElementById('quick-media-input');
    
    // Disable button initially until editor is ready
    if (addMediaBtn) {
        addMediaBtn.disabled = true;
        addMediaBtn.title = 'Đang khởi tạo editor...';
    }
    
    if (addMediaBtn && quickMediaInput) {
        addMediaBtn.addEventListener('click', function() {
            if (!window.editor) {
                alert('Editor chưa sẵn sàng. Vui lòng đợi một chút và thử lại.');
                return;
            }
            quickMediaInput.click();
        });
        quickMediaInput.addEventListener('change', async function() {
            if (!this.files || this.files.length === 0) return;
            
            if (!window.editor) {
                alert('Editor chưa sẵn sàng. Vui lòng đợi một chút và thử lại.');
                this.value = '';
                return;
            }
            
            let success = 0;
            let errors = [];
            for (const file of this.files) {
                try { 
                    await uploadImageAndInsert(file); 
                    success++; 
                } catch (e) {
                    console.error('Upload error:', e);
                    const errorMsg = e.message || 'Lỗi không xác định';
                    errors.push(file.name + ' (' + errorMsg + ')');
                }
            }
            
            if (success > 0 && errors.length === 0) {
                showSuccessMessage(`Đã tải và chèn ${success}/${this.files.length} ảnh thành công.`);
            } else if (success > 0 && errors.length > 0) {
                showSuccessMessage(`Đã tải ${success}/${this.files.length} ảnh.`);
                alert('Không thể tải lên một số ảnh:\n' + errors.join('\n'));
            } else if (errors.length > 0) {
                alert('Không thể tải lên ảnh:\n' + errors.join('\n'));
            }
            this.value = '';
        });
    }

    // Featured image preview like WordPress
    const featuredInput = document.getElementById('featured-image');
    const featuredPreviewWrap = document.getElementById('featured-preview');
    const featuredImg = document.getElementById('featured-img');
    const featuredPlaceholder = document.getElementById('featured-placeholder');
    const removeFeaturedBtn = document.getElementById('remove-featured');

    if (featuredInput) {
        featuredInput.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                if (featuredImg) {
                    featuredImg.src = e.target.result;
                    if (featuredPreviewWrap) featuredPreviewWrap.style.display = 'block';
                    if (featuredPlaceholder) featuredPlaceholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        });
    }

    if (removeFeaturedBtn) {
        removeFeaturedBtn.addEventListener('click', function() {
            if (featuredInput) featuredInput.value = '';
            if (featuredPreviewWrap) featuredPreviewWrap.style.display = 'none';
            if (featuredPlaceholder) featuredPlaceholder.style.display = 'block';
        });
    }

    // Bỏ toàn bộ logic xem trước/ quản lý danh sách ảnh được chọn

    async function uploadImageAndInsert(file) {
        if (!window.editor) {
            throw new Error('Editor chưa được khởi tạo. Vui lòng đợi một chút và thử lại.');
        }
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            throw new Error('File phải là ảnh (jpeg, png, jpg, gif, webp)');
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            throw new Error('Kích thước ảnh không được vượt quá 5MB');
        }
        
        const formData = new FormData();
        formData.append('image', file);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        try {
            const res = await fetch("{{ route('admin.posts.upload-image') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                body: formData
            });
            
            if (!res.ok) {
                let errorMessage = 'Không thể tải ảnh lên server.';
                try {
                    const errorText = await res.text();
                    const errorJson = JSON.parse(errorText);
                    if (errorJson.error && errorJson.error.message) {
                        errorMessage = errorJson.error.message;
                    } else if (errorJson.message) {
                        errorMessage = errorJson.message;
                    } else {
                        errorMessage = errorText || errorMessage;
                    }
                } catch (e) {
                    // Nếu không parse được JSON, sử dụng status text
                    errorMessage = `Lỗi ${res.status}: ${res.statusText}`;
                }
                throw new Error(errorMessage);
            }
            
            const data = await res.json();
            if (!data) {
                throw new Error('Không nhận được phản hồi từ server');
            }
            
            if (data.error) {
                throw new Error(data.error.message || 'Lỗi từ server');
            }
            
            if (!data.url) {
                throw new Error('Không nhận được URL ảnh từ server');
            }
            
            const imageHtml = `<figure class="image"><img src="${data.url}" alt="${file.name || 'Image'}" style="max-width:100%;height:auto;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);"></figure>`;
            
            try {
                // Sử dụng insertContent API của CKEditor 5
                const currentData = window.editor.getData();
                const newContent = currentData + (currentData && !currentData.endsWith('</p>') ? '<p></p>' : '') + imageHtml;
                window.editor.setData(newContent);
                
                // Scroll to bottom để hiển thị ảnh vừa chèn
                setTimeout(() => {
                    const editorElement = window.editor.ui.getEditableElement();
                    if (editorElement) {
                        editorElement.scrollTop = editorElement.scrollHeight;
                    }
                }, 100);
            } catch (e) {
                console.error('Error inserting image into editor:', e);
                // Fallback: append to textarea directly
                const textarea = document.getElementById('content');
                if (textarea) {
                    textarea.value = (textarea.value || '') + imageHtml;
                }
                throw new Error('Không thể chèn ảnh vào editor: ' + e.message);
            }
        } catch (e) {
            console.error('Upload error:', e);
            throw e;
        }
    }

    window.addSingleImageToEditor = async function(index) {
        if (window.editor && window.selectedImages[index]) {
            const file = window.selectedImages[index];
            try {
                await uploadImageAndInsert(file);
                showSuccessMessage(`Đã tải và chèn ảnh "${file.name}".`);
            } catch (e) {
                alert('Không thể tải ảnh lên. Vui lòng thử lại.');
            }
            window.selectedImages.splice(index, 1);
            if (typeof displaySelectedImages === 'function') {
                displaySelectedImages();
            }
        }
    };

    window.addAllImagesToEditor = async function() {
        if (window.editor && window.selectedImages.length > 0) {
            const files = [...window.selectedImages];
            let success = 0;
            for (const f of files) {
                try { await uploadImageAndInsert(f); success++; } catch (e) {}
            }
            showSuccessMessage(`Đã tải và chèn ${success}/${files.length} ảnh.`);
            window.selectedImages = [];
            if (typeof displaySelectedImages === 'function') {
                displaySelectedImages();
            }
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

