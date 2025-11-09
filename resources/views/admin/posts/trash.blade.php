@extends('admin.layouts.app')

@section('title', 'Thùng rác Bài viết - CLB Admin')

@section('content')
<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-trash"></i> Thùng rác Bài viết</h1>
        <div>
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

<!-- Bộ lọc và tìm kiếm -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.posts.trash') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Tìm kiếm</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Tìm theo tiêu đề hoặc nội dung...">
            </div>
            <div class="col-md-3">
                <label for="club_id" class="form-label">Câu lạc bộ</label>
                <select class="form-select" id="club_id" name="club_id">
                    <option value="">Tất cả câu lạc bộ</option>
                    @foreach($clubs as $club)
                        <option value="{{ $club->id }}" {{ request('club_id') == $club->id ? 'selected' : '' }}>
                            {{ $club->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="type" class="form-label">Loại bài viết</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Tất cả loại</option>
                    <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Bài viết thường</option>
                    <option value="announcement" {{ request('type') == 'announcement' ? 'selected' : '' }}>Thông báo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách bài viết trong thùng rác -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-trash"></i> Bài viết đã xóa 
            <span class="badge bg-danger">{{ $posts->total() }}</span>
        </h5>
    </div>
    <div class="card-body">
        @if($posts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="25%">Tiêu đề</th>
                            <th width="10%">Loại</th>
                            <th width="15%">Câu lạc bộ</th>
                            <th width="15%">Tác giả</th>
                            <th width="15%">Ngày xóa</th>
                            <th width="15%">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $index => $post)
                            <tr>
                                <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</td>
                                <td>
                                    <strong>{{ $post->title }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $post->type === 'announcement' ? 'danger' : 'primary' }}">
                                        {{ $post->type === 'announcement' ? 'Thông báo' : 'Bài viết' }}
                                    </span>
                                </td>
                                <td>{{ $post->club->name ?? 'Không xác định' }}</td>
                                <td>{{ $post->user->name ?? 'Không xác định' }}</td>
                                <td>
                                    <span class="text-muted">{{ $post->deleted_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.posts.show', $post->id) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    onclick="return confirm('Bạn có chắc muốn khôi phục bài viết này?')" title="Khôi phục">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.posts.force-delete', $post->id) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn bài viết này? Hành động này không thể hoàn tác!')" title="Xóa vĩnh viễn">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-trash fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Thùng rác trống</h5>
                <p class="text-muted">Không có bài viết nào trong thùng rác.</p>
                <a href="{{ route('admin.posts') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách bài viết
                </a>
            </div>
        @endif
    </div>
</div>

<style>
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.badge {
    font-size: 0.75em;
}

.text-muted {
    color: #6c757d !important;
}
</style>
@endsection
