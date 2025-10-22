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
                    <form action="{{ route('admin.club-resources.update', $resource->id) }}" method="POST" enctype="multipart/form-data">
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
                                        
                                        <div class="form-group">
                                            <label for="file">File tài nguyên mới</label>
                                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                                   id="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.mov">
                                            <small class="form-text text-muted">
                                                Hỗ trợ: PDF, DOC, XLS, PPT, JPG, PNG, MP4, AVI (Tối đa 20MB)
                                            </small>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
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
                                            <input type="url" class="form-control @error('external_link') is-invalid @enderror" 
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
                                    <a href="{{ route('admin.club-resources.show', $resource->id) }}" class="btn btn-secondary btn-lg ml-2">
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
document.addEventListener('DOMContentLoaded', function() {
    // File input change handler (if needed for other purposes)
    const fileInput = document.getElementById('file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            console.log('File selected:', e.target.files[0]?.name);
        });
    }
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
@endsection
