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

                        <div class="mb-3">
                            <label class="form-label">Hình ảnh</label>
                            <input type="file" class="form-control" name="images[]" accept="image/*" multiple id="imagesInput">
                            <small class="form-text text-muted">Chọn nhiều hình ảnh cùng lúc (JPG, PNG, GIF, WEBP - Tối đa 2MB mỗi ảnh)</small>
                            
                            @if($post->image)
                                <div class="mt-3">
                                    <p class="mb-2"><strong>Ảnh hiện tại:</strong></p>
                                    <img src="{{ asset($post->image) }}" alt="Current image" style="max-width: 200px; border-radius: 8px;">
                                </div>
                            @endif
                            
                            <div class="mt-3" id="imagesPreviewWrap" style="display:none;">
                                <p class="mb-2"><strong>Ảnh mới sẽ được thêm:</strong></p>
                                <div class="row" id="imagesPreviewContainer">
                                    <!-- Ảnh preview sẽ được thêm vào đây -->
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
    const textarea = document.querySelector('#content');
    
    if (typeof ClassicEditor === 'undefined') {
        console.error('ClassicEditor is not loaded!');
        return;
    }
    
    if (!textarea) {
        console.error('Textarea with id "content" not found');
        return;
    }
    
    ClassicEditor
        .create(textarea, {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', '|',
                    'link', '|',
                    'undo', 'redo'
                ]
            },
            language: 'vi'
        })
        .then(editor => {
            console.log('CKEditor initialized successfully');
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });

    // Preview images
    const input = document.getElementById('imagesInput');
    const wrap = document.getElementById('imagesPreviewWrap');
    const container = document.getElementById('imagesPreviewContainer');

    input?.addEventListener('change', function(e) {
        const files = e.target.files;
        if (!files || files.length === 0) {
            wrap.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        container.innerHTML = '';

        // Hiển thị preview
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(ev) {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-3';
                col.innerHTML = `
                    <div style="position: relative; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; background: #f8f9fa;">
                        <img src="${ev.target.result}" alt="Preview ${index + 1}" style="width: 100%; height: 150px; object-fit: cover; display: block;">
                    </div>
                `;
                container.appendChild(col);
            };
            reader.readAsDataURL(file);
        });

        wrap.style.display = 'block';
    });
});
</script>
@endsection

