@extends('admin.layouts.app')

@section('title', 'Chi tiết Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Chi tiết Bài viết</h1>
        <div>
            <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <!-- Nội dung bài viết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Nội dung bài viết</h5>
            </div>
            <div class="card-body">
                <h2 class="mb-3">{{ $post->title }}</h2>
                
                @if($post->image)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $post->image) }}" alt="{{ $post->title }}" class="img-fluid rounded" style="max-height: 400px; width: 100%; object-fit: cover;">
                    </div>
                @endif
                
                <div class="content-body">
                    {!! $post->content !!}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin bài viết -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bài viết</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Tiêu đề:</strong>
                    <p class="mt-1">{{ $post->title }}</p>
                </div>
                
                
                <div class="mb-3">
                    <strong>Câu lạc bộ:</strong>
                    <p class="mt-1">
                        <span class="badge badge-primary">{{ $post->club->name }}</span>
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong>Tác giả:</strong>
                    <p class="mt-1">{{ $post->user->name }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Loại bài viết:</strong>
                    <p class="mt-1">
                        @if($post->type == 'post')
                            <span class="badge badge-info">Bài viết thường</span>
                        @elseif($post->type == 'announcement')
                            <span class="badge badge-warning">Thông báo</span>
                        @endif
                    </p>
                </div>
                
                <div class="mb-3">
                    <strong>Trạng thái:</strong>
                    <p class="mt-1">
                        @if($post->status == 'published')
                            <span class="badge badge-success">Công khai</span>
                        @elseif($post->status == 'members_only')
                            <span class="badge badge-info">Chỉ thành viên CLB</span>
                        @elseif($post->status == 'hidden')
                            <span class="badge badge-secondary">Ẩn</span>
                        @elseif($post->status == 'deleted')
                            <span class="badge badge-danger">Đã xóa</span>
                        @endif
                        
                        @if($post->deleted_at)
                            <span class="badge badge-warning ms-2">Trong thùng rác</span>
                        @endif
                    </p>
                </div>
                
                @if($post->deleted_at)
                <div class="mb-3">
                    <strong>Ngày xóa:</strong>
                    <p class="mt-1 text-danger">{{ $post->deleted_at->format('d/m/Y H:i') }}</p>
                </div>
                @endif
                
                <div class="mb-3">
                    <strong>Ngày tạo:</strong>
                    <p class="mt-1">{{ $post->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <strong>Cập nhật lần cuối:</strong>
                    <p class="mt-1">{{ $post->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
        
        <!-- Thống kê -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-1">{{ $post->view_count ?? 0 }}</h4>
                            <small class="text-muted">Lượt xem</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-1">{{ $post->download_count ?? 0 }}</h4>
                        <small class="text-muted">Lượt tải</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hành động -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> Hành động</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($post->deleted_at)
                        <!-- Bài viết đã bị xóa -->
                        <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn khôi phục bài viết này?')">
                                <i class="fas fa-undo"></i> Khôi phục bài viết
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.posts.force-delete', $post->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn bài viết này? Hành động này không thể hoàn tác!')">
                                <i class="fas fa-trash"></i> Xóa vĩnh viễn
                            </button>
                        </form>
                    @else
                        <!-- Bài viết chưa bị xóa -->
                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        
                        @if($post->status == 'published')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="members_only">
                                <button type="submit" class="btn btn-info w-100" onclick="return confirm('Bạn có chắc muốn chuyển bài viết này thành chỉ thành viên CLB?')">
                                    <i class="fas fa-users"></i> Chỉ thành viên CLB
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="hidden">
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Bạn có chắc muốn ẩn bài viết này?')">
                                    <i class="fas fa-eye-slash"></i> Ẩn bài viết
                                </button>
                            </form>
                        @elseif($post->status == 'members_only')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn công khai bài viết này?')">
                                    <i class="fas fa-eye"></i> Công khai
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="hidden">
                                <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Bạn có chắc muốn ẩn bài viết này?')">
                                    <i class="fas fa-eye-slash"></i> Ẩn bài viết
                                </button>
                            </form>
                        @elseif($post->status == 'hidden')
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn công khai bài viết này?')">
                                    <i class="fas fa-eye"></i> Công khai
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="members_only">
                                <button type="submit" class="btn btn-info w-100" onclick="return confirm('Bạn có chắc muốn chuyển bài viết này thành chỉ thành viên CLB?')">
                                    <i class="fas fa-users"></i> Chỉ thành viên CLB
                                </button>
                            </form>
                        @endif
                        
                        <form method="POST" action="{{ route('admin.posts.status', $post->id) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="deleted">
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Bạn có chắc muốn xóa bài viết này? Hành động này không thể hoàn tác!')">
                                <i class="fas fa-trash"></i> Xóa bài viết
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.content-body {
    line-height: 1.6;
}

.content-body h1, .content-body h2, .content-body h3, .content-body h4, .content-body h5, .content-body h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.content-body p {
    margin-bottom: 1rem;
}

.content-body ul, .content-body ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.content-body blockquote {
    border-left: 4px solid #007bff;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #6c757d;
}

.content-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.content-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.content-body table th,
.content-body table td {
    border: 1px solid #dee2e6;
    padding: 0.75rem;
    text-align: left;
}

.content-body table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.badge {
    font-size: 0.875em;
    padding: 0.375rem 0.75rem;
}

.badge-primary { background-color: #007bff; }
.badge-info { background-color: #17a2b8; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-success { background-color: #28a745; }
.badge-secondary { background-color: #6c757d; }
.badge-danger { background-color: #dc3545; }
</style>
@endsection
