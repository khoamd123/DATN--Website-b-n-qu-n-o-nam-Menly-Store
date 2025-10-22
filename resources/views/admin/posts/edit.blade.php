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
        <div class="col-md-8">
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
                            <input type="text" class="form-control" name="title" value="{{ old('title', $post->title) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content', $post->content) }}</textarea>
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
                                <option value="hidden" {{ old('status', $post->status) == 'hidden' ? 'selected' : '' }}>Ẩn</option>
                                <option value="deleted" {{ old('status', $post->status) == 'deleted' ? 'selected' : '' }}>Đã xóa</option>
                            </select>
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

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tiêu đề:</strong> {{ $post->title }}</p>
                    <p><strong>Câu lạc bộ:</strong> {{ $post->club->name ?? 'N/A' }}</p>
                    <p><strong>Người tạo:</strong> {{ $post->user->name ?? 'N/A' }}</p>
                    <p><strong>Loại:</strong> 
                        @if($post->type == 'post')
                            <span class="badge bg-info">Bài viết thường</span>
                        @else
                            <span class="badge bg-warning">Thông báo</span>
                        @endif
                    </p>
                    <p><strong>Trạng thái:</strong> 
                        @php
                            $statusColors = [
                                'published' => 'success',
                                'hidden' => 'warning',
                                'deleted' => 'danger'
                            ];
                            $statusLabels = [
                                'published' => 'Công khai',
                                'hidden' => 'Ẩn',
                                'deleted' => 'Đã xóa'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$post->status] ?? 'secondary' }}">
                            {{ $statusLabels[$post->status] ?? ucfirst($post->status) }}
                        </span>
                    </p>
                    <p><strong>Ngày tạo:</strong> {{ $post->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Cập nhật lần cuối:</strong> {{ $post->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Thống kê</h5>
                </div>
                <div class="card-body">
                    @php
                        $commentsCount = \App\Models\PostComment::where('post_id', $post->id)->count();
                    @endphp
                    <p class="text-center">
                        <strong>{{ $commentsCount }}</strong><br>
                        <small class="text-muted">bình luận</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    ClassicEditor
        .create(document.querySelector('#content'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', '|',
                    'link', 'imageUpload', '|',
                    'undo', 'redo'
                ]
            },
            language: 'vi',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:full',
                    'imageStyle:side'
                ]
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            console.log('CKEditor initialized successfully');
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
</script>
@endsection





