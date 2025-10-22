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
                        
                        <div class="mb-3">
                            <label class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content">{{ old('content') }}</textarea>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="mb-3">
                            <label class="form-label">Hình ảnh bài viết</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                                    <small class="form-text text-muted">Chọn hình ảnh để hiển thị trong bài viết (JPG, PNG, GIF)</small>
                                </div>
                                <div class="col-md-6">
                                    <div id="image-preview" class="text-center" style="display: none;">
                                        <img id="preview-img" class="img-fluid" style="max-height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <p class="mt-2 text-muted" id="image-name"></p>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeImage()">
                                            <i class="fas fa-trash"></i> Xóa ảnh
                                        </button>
                                    </div>
                                    <div id="no-image" class="text-center text-muted p-3" style="border: 2px dashed #dee2e6; border-radius: 8px;">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <p class="mb-0">Chưa có hình ảnh</p>
                                    </div>
                                </div>
                            </div>
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

    // Image preview functions
    window.previewImage = function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-name').textContent = input.files[0].name;
                document.getElementById('image-preview').style.display = 'block';
                document.getElementById('no-image').style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeImage = function() {
        document.getElementById('image').value = '';
        document.getElementById('image-preview').style.display = 'none';
        document.getElementById('no-image').style.display = 'block';
    };
});
</script>
@endsection

