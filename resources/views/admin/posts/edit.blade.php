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
                        
                        <div class="row">
                            <div class="col-lg-8">
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $post->title) }}" required>
                        </div>
                                
                                <!-- Toolbar row: Media button (like WordPress) -->
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <button type="button" id="add-media-btn" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-photo-video"></i> Thêm Media
                                    </button>
                                    <input type="file" id="quick-media-input" accept="image/*" multiple style="display:none">
                                    <small class="text-muted">Nhấp Thêm Media để chọn ảnh và chèn trực tiếp.</small>
                                </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content">{{ old('content', $post->content) }}</textarea>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card mb-3">
                                    <div class="card-header"><strong>Đăng bài viết</strong></div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                            <select class="form-select" name="status" required>
                                                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Công khai</option>
                                                <option value="members_only" {{ old('status', $post->status) == 'members_only' ? 'selected' : '' }}>Chỉ thành viên CLB</option>
                                                <option value="hidden" {{ old('status', $post->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                                <option value="deleted" {{ old('status', $post->status) == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="fas fa-save"></i> Cập nhật bài viết
                                            </button>
                                            <a href="{{ route('admin.posts') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i></a>
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
                                                    <option value="{{ $club->id }}" {{ old('club_id', $post->club_id) == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Loại bài viết</label>
                                            <select class="form-select" name="type" required>
                                                <option value="post" {{ old('type', $post->type) == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                                                <option value="announcement" {{ old('type', $post->type) == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header"><strong>Ảnh đại diện</strong></div>
                                    <div class="card-body">
                                        <input type="file" class="form-control mb-2" id="featured-image" name="image" accept="image/*">
                                        <div id="featured-preview" class="text-center" style="display: {{ $post->image ? 'block' : 'none' }};">
                                            @if($post->image)
                                                <img id="featured-img" src="{{ asset($post->image) }}" alt="Ảnh đại diện" class="img-fluid rounded border" style="max-height: 200px; object-fit: cover;">
                                            @else
                                                <img id="featured-img" src="#" alt="Ảnh đại diện" class="img-fluid rounded border" style="max-height: 200px; object-fit: cover; display:none;">
                                            @endif
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="remove-featured">Xóa ảnh</button>
                                            </div>
                                        </div>
                                        <div id="featured-placeholder" class="text-center text-muted p-3" style="border: 2px dashed #dee2e6; border-radius: 8px; display: {{ $post->image ? 'none' : 'block' }};">
                                            <i class="fas fa-image fa-2x mb-2"></i>
                                            <p class="mb-0">Chưa chọn ảnh đại diện</p>
                                        </div>
                                        <input type="hidden" name="remove_image" id="remove_image" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Đã loại bỏ khu vực "Hình ảnh bài viết" để đơn giản giao diện -->

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

    // Bỏ cơ chế thư viện ảnh cũ – không cần mảng selectedImages
    // Featured image preview like create page
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
                    featuredImg.style.display = 'block';
                    if (featuredPreviewWrap) featuredPreviewWrap.style.display = 'block';
                    if (featuredPlaceholder) featuredPlaceholder.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
            // reset remove flag if user picks new image
            const removeInput = document.getElementById('remove_image');
            if (removeInput) removeInput.value = '0';
        });
    }

    if (removeFeaturedBtn) {
        removeFeaturedBtn.addEventListener('click', function() {
            const removeInput = document.getElementById('remove_image');
            if (removeInput) removeInput.value = '1';
            if (featuredInput) featuredInput.value = '';
            if (featuredPreviewWrap) featuredPreviewWrap.style.display = 'none';
            if (featuredPlaceholder) featuredPlaceholder.style.display = 'block';
        });
    }


    // Media button: choose files and insert immediately (same as create page)
    const addMediaBtn = document.getElementById('add-media-btn');
    const quickMediaInput = document.getElementById('quick-media-input');
    if (addMediaBtn && quickMediaInput) {
        addMediaBtn.addEventListener('click', function() {
            quickMediaInput.click();
        });
        quickMediaInput.addEventListener('change', async function() {
            if (!this.files || this.files.length === 0) return;
            let success = 0;
            for (const file of this.files) {
                try { await uploadImageAndInsert(file); success++; } catch (e) {}
            }
            showSuccessMessage(`Đã tải và chèn ${success}/${this.files.length} ảnh.`);
            this.value = '';
        });
    }

    // Loại bỏ toàn bộ logic xem trước/chèn ảnh base64 cũ

    //

    async function uploadImageAndInsert(file) {
        const formData = new FormData();
        formData.append('image', file);
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const res = await fetch("{{ route('admin.posts.upload-image') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token },
            body: formData
        });
        if (!res.ok) throw new Error('Upload failed');
        const { url } = await res.json();
        const imageHtml = `<figure class="image"><img src="${url}" alt="${file.name}" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></figure>`;
        window.editor.model.change(writer => {
            const viewFragment = window.editor.data.processor.toView(imageHtml);
            const modelFragment = window.editor.data.toModel(viewFragment);
            const root = window.editor.model.document.getRoot();
            const lastPosition = writer.createPositionAt(root, 'end');
            window.editor.model.insertContent(modelFragment, lastPosition);
        });
    }

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

    // Bỏ toàn bộ logic gallery/đánh dấu xoá attachment (đã bỏ khu vực hiển thị)

});
</script>
@endsection

