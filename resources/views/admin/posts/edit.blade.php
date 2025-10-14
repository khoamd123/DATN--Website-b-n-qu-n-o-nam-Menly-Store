@extends('admin.layouts.app')

@section('title', 'Sửa Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <h1>Sửa Bài viết</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.posts') }}">Quản lý Bài viết</a></li>
            <li class="breadcrumb-item active">Sửa bài viết #{{ $post->id }}</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.posts.update', $post->id) }}">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <!-- Tiêu đề -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $post->title) }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nội dung -->
                    <div class="mb-3">
                        <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" 
                                  name="content" 
                                  rows="10" 
                                  required>{{ old('content', $post->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Thông tin bài viết -->
                    <div class="card bg-light mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Thông tin bài viết</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> {{ $post->id }}</p>
                            <p><strong>Slug:</strong> {{ $post->slug }}</p>
                            <p><strong>Tác giả:</strong> {{ $post->user->name ?? 'Không xác định' }}</p>
                            <p><strong>Ngày tạo:</strong> {{ $post->created_at->format('d/m/Y H:i') }}</p>
                            <p><strong>Cập nhật cuối:</strong> {{ $post->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Câu lạc bộ -->
                    <div class="mb-3">
                        <label for="club_id" class="form-label">Câu lạc bộ <span class="text-danger">*</span></label>
                        <select class="form-select @error('club_id') is-invalid @enderror" 
                                id="club_id" 
                                name="club_id" 
                                required>
                            <option value="">Chọn câu lạc bộ</option>
                            @foreach($clubs as $club)
                                <option value="{{ $club->id }}" 
                                        {{ old('club_id', $post->club_id) == $club->id ? 'selected' : '' }}>
                                    {{ $club->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('club_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Loại bài viết -->
                    <div class="mb-3">
                        <label for="type" class="form-label">Loại bài viết <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" 
                                name="type" 
                                required>
                            <option value="">Chọn loại</option>
                            <option value="post" {{ old('type', $post->type) == 'post' ? 'selected' : '' }}>Bài viết</option>
                            <option value="announcement" {{ old('type', $post->type) == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Trạng thái -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="">Chọn trạng thái</option>
                            <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                            <option value="hidden" {{ old('status', $post->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preview -->
                    <div class="mb-3">
                        <label class="form-label">Preview</label>
                        <div class="border rounded p-3 bg-light" id="preview">
                            <div class="mb-2">
                                <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }}">
                                    {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                                </span>
                            </div>
                            <h5>{{ $post->title }}</h5>
                            <div class="text-muted small mb-2">{{ $post->created_at->format('d/m/Y') }}</div>
                            <div>{{ Str::limit($post->content, 200) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <div>
                    <button type="button" class="btn btn-outline-primary me-2" onclick="previewPost()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật bài viết
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function previewPost() {
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;
    const type = document.getElementById('type').value;
    
    if (title && content) {
        const preview = document.getElementById('preview');
        const typeLabel = type === 'announcement' ? 'Thông báo' : 'Bài viết';
        
        preview.innerHTML = `
            <div class="mb-2">
                <span class="badge bg-${type === 'announcement' ? 'danger' : 'primary'}">${typeLabel}</span>
            </div>
            <h5>${title}</h5>
            <div class="text-muted small mb-2">${new Date().toLocaleDateString('vi-VN')}</div>
            <div>${content.substring(0, 200)}${content.length > 200 ? '...' : ''}</div>
        `;
    }
}

// Auto preview when typing
document.getElementById('title').addEventListener('input', previewPost);
document.getElementById('content').addEventListener('input', previewPost);
document.getElementById('type').addEventListener('change', previewPost);
</script>
@endsection
